<?php

declare(strict_types=1);

namespace App\State\Stats;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\StatsOutput;
use App\Service\StatsService;

/** @implements ProviderInterface<StatsOutput> */
final class StatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly StatsService $statsService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): StatsOutput
    {
        return $this->statsService->getStats();
    }
}
