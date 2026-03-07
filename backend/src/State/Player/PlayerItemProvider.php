<?php

namespace App\State\Player;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Output\PlayerOutput;
use App\Repository\PlayerRepository;
use App\Service\DtoMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlayerOutput> */
final class PlayerItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly DtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PlayerOutput
    {
        $id     = isset($uriVariables['id']) && is_scalar($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $player = $this->playerRepository->find($id);

        if ($player === null) {
            throw new NotFoundHttpException(sprintf('Player %d not found.', $id));
        }

        return $this->mapper->playerToOutput($player);
    }
}
