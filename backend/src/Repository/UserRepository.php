<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findBySlug(string $slug): ?User
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function countSearch(string $search): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.deletedAt IS NULL');

        if ($search !== '') {
            $qb->andWhere('u.username LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByConfirmationToken(string $token): ?User
    {
        return $this->findOneBy(['confirmationToken' => $token]);
    }

    /**
     * @return User[]
     */
    public function searchPaginated(string $search, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL');

        if ($search !== '') {
            $qb->andWhere('u.username LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        /** @var User[] $result */
        $result = $qb
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
