<?php

declare(strict_types=1);

namespace App\State\Match;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\MatchOutput;
use App\Repository\SoloMatchRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<MatchOutput> */
final class MatchItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly SoloMatchRepository $soloMatchRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MatchOutput
    {
        $id    = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $match = $this->soloMatchRepository->find($id);

        if ($match === null) {
            throw new NotFoundHttpException(sprintf('Match %d not found.', $id));
        }

        return $this->mapper->matchToOutput($match);
    }
}
