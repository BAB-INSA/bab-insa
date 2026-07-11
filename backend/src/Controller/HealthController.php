<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1');
            $dbStatus = 'ok';
        } catch (\Throwable) {
            $dbStatus = 'error';
        }

        $status = $dbStatus === 'ok' ? 'ok' : 'degraded';

        return $this->json([
            'status' => $status,
            'database' => $dbStatus,
        ], $status === 'ok' ? 200 : 503);
    }
}
