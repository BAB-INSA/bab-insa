<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly ValidatorInterface $validator,
        private readonly MailerInterface $mailer,
    ) {
    }

    // -------------------------------------------------------------------------
    // POST /auth/register
    // -------------------------------------------------------------------------
    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $errors = $this->validateFields($data, [
            'email'    => [new Assert\NotBlank(), new Assert\Email()],
            'username' => [new Assert\NotBlank(), new Assert\Length(min: 3, max: 100)],
            'password' => [new Assert\NotBlank(), new Assert\Length(min: 6)],
        ]);
        if ($errors !== []) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($this->userRepository->findByEmail($data['email']) !== null) {
            return $this->json(['message' => 'Email already in use.'], Response::HTTP_CONFLICT);
        }
        if ($this->userRepository->findByUsername($data['username']) !== null) {
            return $this->json(['message' => 'Username already in use.'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush(); // flush pour obtenir user.id avant de créer le Player

        $player = new Player();
        $player->setUser($user);
        $player->setUsername($user->getUsername());
        $this->em->persist($player);
        $this->em->flush();

        $jwt = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->create($user);

        return $this->json([
            'token'         => $jwt,
            'refresh_token' => $refreshToken->getToken(),
            'user'          => $this->serializeUser($user),
        ], Response::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------
    // POST /auth/login
    // -------------------------------------------------------------------------
    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $errors = $this->validateFields($data, [
            'email'    => [new Assert\NotBlank(), new Assert\Email()],
            'password' => [new Assert\NotBlank()],
        ]);
        if ($errors !== []) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->userRepository->findByEmail($data['email']);
        if ($user === null || !$user->isEnabled()) {
            return $this->json(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        // Mise à jour lastLogin + compteur connexion
        $user->setLastLogin(new \DateTimeImmutable());
        $user->incrementNbConnexion();
        $this->em->flush();

        $jwt = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->create($user);

        return $this->json([
            'token'         => $jwt,
            'refresh_token' => $refreshToken->getToken(),
            'user'          => $this->serializeUser($user),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /auth/refresh
    // -------------------------------------------------------------------------
    #[Route('/refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $tokenValue = $data['refresh_token'] ?? '';

        if ($tokenValue === '') {
            return $this->json(['message' => 'Refresh token required.'], Response::HTTP_BAD_REQUEST);
        }

        $refreshToken = $this->refreshTokenService->findValid($tokenValue);
        if ($refreshToken === null) {
            return $this->json(['message' => 'Invalid or expired refresh token.'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $refreshToken->getUser();
        if (!$user->isEnabled()) {
            return $this->json(['message' => 'Account disabled.'], Response::HTTP_UNAUTHORIZED);
        }

        $jwt = $this->jwtManager->create($user);

        return $this->json(['token' => $jwt]);
    }

    // -------------------------------------------------------------------------
    // POST /auth/logout
    // -------------------------------------------------------------------------
    #[Route('/logout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function logout(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $tokenValue = $data['refresh_token'] ?? '';

        if ($tokenValue !== '') {
            $this->refreshTokenService->revoke($tokenValue);
        }

        return $this->json(['message' => 'Logged out successfully.']);
    }

    // -------------------------------------------------------------------------
    // POST /auth/logout-all
    // -------------------------------------------------------------------------
    #[Route('/logout-all', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function logoutAll(#[CurrentUser] User $user): JsonResponse
    {
        $this->refreshTokenService->revokeAll($user);

        return $this->json(['message' => 'All sessions revoked.']);
    }

    // -------------------------------------------------------------------------
    // POST /auth/reset-password/send-link
    // -------------------------------------------------------------------------
    #[Route('/reset-password/send-link', methods: ['POST'])]
    public function sendResetLink(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $email = $data['email'] ?? '';

        // Toujours répondre 200 pour ne pas divulguer si l'email existe
        $user = $this->userRepository->findByEmail($email);
        if ($user !== null && $user->isEnabled()) {
            $token = bin2hex(random_bytes(32));
            $user->setConfirmationToken($token);
            $user->setPasswordRequestedAt(new \DateTimeImmutable());
            $this->em->flush();

            $this->sendPasswordResetEmail($user, $token);
        }

        return $this->json(['message' => 'If this email exists, a reset link has been sent.']);
    }

    // -------------------------------------------------------------------------
    // POST /auth/reset-password/confirm
    // -------------------------------------------------------------------------
    #[Route('/reset-password/confirm', methods: ['POST'])]
    public function confirmPasswordReset(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $errors = $this->validateFields($data, [
            'token'       => [new Assert\NotBlank()],
            'newPassword' => [new Assert\NotBlank(), new Assert\Length(min: 6)],
        ]);
        if ($errors !== []) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->userRepository->findByConfirmationToken($data['token']);
        if ($user === null) {
            return $this->json(['message' => 'Invalid or expired token.'], Response::HTTP_BAD_REQUEST);
        }

        // Token valide 24h (comme l'API Go)
        $requestedAt = $user->getPasswordRequestedAt();
        if ($requestedAt === null || $requestedAt < new \DateTimeImmutable('-24 hours')) {
            return $this->json(['message' => 'Token expired.'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $data['newPassword']));
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $this->em->flush();

        return $this->json(['message' => 'Password reset successfully.']);
    }

    // -------------------------------------------------------------------------
    // POST /auth/change-password
    // -------------------------------------------------------------------------
    #[Route('/change-password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changePassword(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = $request->toArray();

        $errors = $this->validateFields($data, [
            'currentPassword' => [new Assert\NotBlank()],
            'newPassword'     => [new Assert\NotBlank(), new Assert\Length(min: 6)],
        ]);
        if ($errors !== []) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
            return $this->json(['message' => 'Current password is incorrect.'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $data['newPassword']));
        $this->em->flush();

        return $this->json(['message' => 'Password changed successfully.']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<\Symfony\Component\Validator\Constraint>> $rules
     * @return array<string, string[]>
     */
    private function validateFields(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $constraints) {
            $violations = $this->validator->validate($data[$field] ?? null, $constraints);
            if (count($violations) > 0) {
                $errors[$field] = array_map(
                    static fn ($v) => $v->getMessage(),
                    iterator_to_array($violations)
                );
            }
        }

        return $errors;
    }

    /** @return array<string, mixed> */
    private function serializeUser(User $user): array
    {
        return [
            'id'       => $user->getId(),
            'email'    => $user->getEmail(),
            'username' => $user->getUsername(),
            'slug'     => $user->getSlug(),
            'roles'    => $user->getRoles(),
            'enabled'  => $user->isEnabled(),
        ];
    }

    private function sendPasswordResetEmail(User $user, string $token): void
    {
        $frontendUrl = is_string($_ENV['FRONTEND_URL'] ?? null) ? $_ENV['FRONTEND_URL'] : 'http://localhost:5173';
        $resetUrl = sprintf('%s/reset-password?token=%s', $frontendUrl, $token);

        $email = (new Email())
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->text(sprintf(
                "Bonjour %s,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe :\n%s\n\nCe lien expire dans 24h.",
                $user->getUsername(),
                $resetUrl,
            ));

        try {
            $this->mailer->send($email);
        } catch (\Throwable) {
            // SMTP non configuré en dev : on log silencieusement
        }
    }
}
