<?php

namespace App\Controller;

use App\Service\AutoValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly AutoValidationService $autoValidationService,
    ) {
    }

    #[Route('/auto-validate', name: 'admin_auto_validate', methods: ['POST'])]
    public function autoValidate(): JsonResponse
    {
        $result = $this->autoValidationService->validatePending();

        return $this->json([
            'message' => 'Auto-validation completed',
            'validated' => $result,
        ]);
    }
}