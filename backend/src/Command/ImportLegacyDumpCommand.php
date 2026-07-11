<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les données de l'ancienne API Go (dump PostgreSQL en SQL brut) dans MySQL.
 *
 * Le fichier attendu est la sortie de : pg_restore -f dump.sql bab_insa_backup.dump
 *
 * Particularités gérées :
 *  - booléens PostgreSQL (t/f) -> 1/0
 *  - rôles Go (user/admin/superAdmin) -> convention Symfony (ROLE_*)
 *  - microsecondes des timestamps tronquées (colonnes DATETIME)
 *  - tournament_teams.joined_at <- created_at (le schéma cible n'a pas de timestamps complets)
 *  - colonnes absentes du schéma cible ignorées (win streaks, opponent_team_id, match_type)
 *  - refresh_tokens non importés (les sessions repartent de zéro)
 *  - lignes soft-deleted ignorées pour les tables cibles sans deleted_at
 */
#[AsCommand(
    name: 'app:import-legacy',
    description: 'Importe les données de l\'ancienne base PostgreSQL (API Go) dans MySQL',
)]
final class ImportLegacyDumpCommand extends Command
{
    /** Ordre d'import (dépendances FK), table cible => table source du dump. */
    private const TABLES = [
        'users'             => 'users',
        'players'           => 'players',
        'teams'             => 'teams',
        'tournaments'       => 'tournaments',
        'matches'           => 'matches',
        'team_matches'      => 'team_matches',
        'tournament_teams'  => 'tournament_teams',
        'elo_history'       => 'elo_history',
        'team_elo_history'  => 'team_elo_history',
    ];

    private const ROLE_MAP = [
        'user'       => 'ROLE_USER',
        'admin'      => 'ROLE_ADMIN',
        'superAdmin' => 'ROLE_SUPER_ADMIN',
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('dump', InputArgument::REQUIRED, 'Chemin du dump SQL brut (sortie de pg_restore)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Ne pas demander de confirmation avant de purger les tables cibles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('dump');
        if (!is_string($path) || !is_readable($path)) {
            $io->error(sprintf('Fichier illisible : %s', is_string($path) ? $path : '(invalide)'));

            return Command::FAILURE;
        }

        $content = (string) file_get_contents($path);
        if (str_starts_with($content, 'PGDMP')) {
            $io->error('Ce fichier est un dump PostgreSQL au format custom. Convertis-le d\'abord en SQL brut :');
            $io->writeln(sprintf('  pg_restore -f dump.sql %s', $path));

            return Command::FAILURE;
        }

        $data = $this->parseCopyBlocks($content);

        $missing = array_diff(array_values(self::TABLES), array_keys($data));
        if ($missing !== []) {
            $io->error(sprintf('Tables absentes du dump : %s', implode(', ', $missing)));

            return Command::FAILURE;
        }

        $io->section('Contenu du dump');
        foreach (self::TABLES as $source) {
            $io->writeln(sprintf('  %-20s %d lignes', $source, count($data[$source]['rows'])));
        }

        if (!$input->getOption('force')) {
            $io->warning('Les tables cibles vont être PURGÉES avant import.');
            if (!$io->confirm('Continuer ?', false)) {
                return Command::SUCCESS;
            }
        }

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            $this->connection->beginTransaction();

            foreach (self::TABLES as $target => $source) {
                // DELETE plutôt que TRUNCATE : TRUNCATE ferait un commit implicite (DDL MySQL)
                $this->connection->executeStatement('DELETE FROM ' . $target);

                $inserted = 0;
                $skipped  = 0;
                foreach ($data[$source]['rows'] as $values) {
                    $row = array_combine($data[$source]['columns'], $values);
                    $mapped = $this->mapRow($target, $row);
                    if ($mapped === null) {
                        $skipped++;
                        continue;
                    }

                    $this->connection->insert($target, $mapped);
                    $inserted++;
                }

                $io->writeln(sprintf('  %-20s %d importées%s', $target, $inserted, $skipped > 0 ? sprintf(', %d ignorées (soft-deleted)', $skipped) : ''));
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }

        $io->success('Import terminé.');
        $io->note('Non importés : refresh_tokens (sessions), colonnes mortes de l\'ancienne base (players.win_streaks, elo_history.opponent_team_id/match_type — plus utilisées par le code Go).');

        return Command::SUCCESS;
    }

    /**
     * Transforme une ligne du dump (colonnes source) en ligne insérable (colonnes cibles).
     * Retourne null si la ligne doit être ignorée.
     *
     * @param array<string, ?string> $row
     * @return array<string, mixed>|null
     */
    private function mapRow(string $target, array $row): ?array
    {
        // Tables cibles sans deleted_at : ne pas ressusciter les lignes soft-deleted
        if (in_array($target, ['tournament_teams', 'elo_history', 'team_elo_history'], true) && ($row['deleted_at'] ?? null) !== null) {
            return null;
        }

        return match ($target) {
            'users' => [
                'id'                    => $row['id'],
                'email'                 => $row['email'],
                'password'              => $row['password'],
                'username'              => $row['username'],
                'slug'                  => $row['slug'],
                'enabled'               => $this->toBool($row['enabled']),
                'roles'                 => $this->mapRoles($row['roles']),
                'last_login'            => $this->toDateTime($row['last_login']),
                'nb_connexion'          => $row['nb_connexion'],
                'confirmation_token'    => $row['confirmation_token'],
                'password_requested_at' => $this->toDateTime($row['password_requested_at']),
                'created_at'            => $this->toDateTime($row['created_at']),
                'updated_at'            => $this->toDateTime($row['updated_at']),
                'deleted_at'            => $this->toDateTime($row['deleted_at']),
            ],
            'players' => [
                'id'                 => $row['id'],
                'username'           => $row['username'],
                'elo_rating'         => $row['elo_rating'],
                '`rank`'             => $row['rank'],
                'total_matches'      => $row['total_matches'],
                'wins'               => $row['wins'],
                'losses'             => $row['losses'],
                'team_elo_rating'    => $row['team_elo_rating'],
                'team_rank'          => $row['team_rank'],
                'team_total_matches' => $row['team_total_matches'],
                'team_wins'          => $row['team_wins'],
                'team_losses'        => $row['team_losses'],
                'created_at'         => $this->toDateTime($row['created_at']),
                'updated_at'         => $this->toDateTime($row['updated_at']),
                'deleted_at'         => $this->toDateTime($row['deleted_at']),
            ],
            'teams' => [
                'id'            => $row['id'],
                'player1_id'    => $row['player1_id'],
                'player2_id'    => $row['player2_id'],
                'name'          => $row['name'],
                'slug'          => $row['slug'],
                'elo_rating'    => $row['elo_rating'],
                'total_matches' => $row['total_matches'],
                'wins'          => $row['wins'],
                'losses'        => $row['losses'],
                'created_at'    => $this->toDateTime($row['created_at']),
                'updated_at'    => $this->toDateTime($row['updated_at']),
                'deleted_at'    => $this->toDateTime($row['deleted_at']),
            ],
            'tournaments' => [
                'id'              => $row['id'],
                'name'            => $row['name'],
                'slug'            => $row['slug'],
                'type'            => $row['type'],
                'status'          => $row['status'],
                'description'     => $row['description'],
                'nb_participants' => $row['nb_participants'],
                'nb_matches'      => $row['nb_matches'],
                'created_at'      => $this->toDateTime($row['created_at']),
                'updated_at'      => $this->toDateTime($row['updated_at']),
                'deleted_at'      => $this->toDateTime($row['deleted_at']),
            ],
            'matches' => [
                'id'            => $row['id'],
                'player1_id'    => $row['player1_id'],
                'player2_id'    => $row['player2_id'],
                'winner_id'     => $row['winner_id'],
                'status'        => $row['status'],
                'tournament_id' => $row['tournament_id'],
                'confirmed_at'  => $this->toDateTime($row['confirmed_at']),
                'created_at'    => $this->toDateTime($row['created_at']),
                'updated_at'    => $this->toDateTime($row['updated_at']),
                'deleted_at'    => $this->toDateTime($row['deleted_at']),
            ],
            'team_matches' => [
                'id'             => $row['id'],
                'team1_id'       => $row['team1_id'],
                'team2_id'       => $row['team2_id'],
                'winner_team_id' => $row['winner_team_id'],
                'status'         => $row['status'],
                'tournament_id'  => $row['tournament_id'],
                'confirmed_at'   => $this->toDateTime($row['confirmed_at']),
                'created_at'     => $this->toDateTime($row['created_at']),
                'updated_at'     => $this->toDateTime($row['updated_at']),
                'deleted_at'     => $this->toDateTime($row['deleted_at']),
            ],
            'tournament_teams' => [
                'id'            => $row['id'],
                'tournament_id' => $row['tournament_id'],
                'team_id'       => $row['team_id'],
                'wins'          => $row['wins'],
                'losses'        => $row['losses'],
                'joined_at'     => $this->toDateTime($row['created_at']),
            ],
            'elo_history' => [
                'id'          => $row['id'],
                'player_id'   => $row['player_id'],
                'match_id'    => $row['match_id'],
                'elo_before'  => $row['elo_before'],
                'elo_after'   => $row['elo_after'],
                'elo_change'  => $row['elo_change'],
                'opponent_id' => $row['opponent_id'],
                'created_at'  => $this->toDateTime($row['created_at']),
                'updated_at'  => $this->toDateTime($row['updated_at']),
            ],
            'team_elo_history' => [
                'id'               => $row['id'],
                'player_id'        => $row['player_id'],
                'team_match_id'    => $row['team_match_id'],
                'elo_before'       => $row['elo_before'],
                'elo_after'        => $row['elo_after'],
                'elo_change'       => $row['elo_change'],
                'opponent_team_id' => $row['opponent_team_id'],
                'created_at'       => $this->toDateTime($row['created_at']),
                'updated_at'       => $this->toDateTime($row['updated_at']),
            ],
            default => throw new \LogicException(sprintf('Table non gérée : %s', $target)),
        };
    }

    /**
     * Extrait les blocs COPY du dump SQL.
     *
     * @return array<string, array{columns: string[], rows: array<int, array<int, ?string>>}>
     */
    private function parseCopyBlocks(string $content): array
    {
        $tables = [];
        $lines  = explode("\n", $content);
        $count  = count($lines);

        for ($i = 0; $i < $count; $i++) {
            if (preg_match('/^COPY public\.(\w+) \(([^)]+)\) FROM stdin;$/', $lines[$i], $m) !== 1) {
                continue;
            }

            $table   = $m[1];
            $columns = array_map(trim(...), explode(',', $m[2]));
            $rows    = [];

            for ($i++; $i < $count && $lines[$i] !== '\.'; $i++) {
                $rows[] = array_map($this->unescapeCopyValue(...), explode("\t", $lines[$i]));
            }

            $tables[$table] = ['columns' => $columns, 'rows' => $rows];
        }

        return $tables;
    }

    /** Décode une valeur du format COPY text de PostgreSQL. */
    private function unescapeCopyValue(string $value): ?string
    {
        if ($value === '\N') {
            return null;
        }

        return str_replace(['\\t', '\\n', '\\r', '\\\\'], ["\t", "\n", "\r", '\\'], $value);
    }

    private function toBool(?string $value): int
    {
        return $value === 't' ? 1 : 0;
    }

    /** Tronque les microsecondes ('2025-11-07 12:08:52.925047' -> '2025-11-07 12:08:52'). */
    private function toDateTime(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) preg_replace('/\.\d+$/', '', $value);
    }

    /** Convertit les rôles Go (["user","admin"]) vers la convention Symfony (["ROLE_USER","ROLE_ADMIN"]). */
    private function mapRoles(?string $json): string
    {
        /** @var string[] $roles */
        $roles = is_string($json) ? (array) json_decode($json, true) : [];

        $mapped = array_values(array_unique(array_map(
            static fn (string $role): string => self::ROLE_MAP[$role] ?? 'ROLE_' . strtoupper($role),
            $roles,
        )));

        return (string) json_encode($mapped !== [] ? $mapped : ['ROLE_USER']);
    }
}
