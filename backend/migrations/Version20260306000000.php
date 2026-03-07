<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale — crée toutes les tables du projet (MySQL 8.0+).
 * Portage du schéma Go (Gin + GORM + PostgreSQL) adapté pour MySQL.
 */
final class Version20260306000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création initiale du schéma : users, refresh_tokens, players, matches, teams, team_matches, tournaments, tournament_teams, elo_history, team_elo_history';
    }

    public function up(Schema $schema): void
    {
        // --- USERS ---
        $this->addSql("
            CREATE TABLE users (
                id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email                   VARCHAR(255) NOT NULL,
                username                VARCHAR(100) NOT NULL,
                password                VARCHAR(255) NOT NULL,
                slug                    VARCHAR(100) NOT NULL,
                enabled                 TINYINT(1) NOT NULL DEFAULT 1,
                roles                   JSON NOT NULL,
                last_login              DATETIME,
                nb_connexion            INT UNSIGNED NOT NULL DEFAULT 0,
                confirmation_token      VARCHAR(255),
                password_requested_at   DATETIME,
                created_at              DATETIME NOT NULL,
                updated_at              DATETIME NOT NULL,
                deleted_at              DATETIME,
                CONSTRAINT uq_users_email    UNIQUE (email),
                CONSTRAINT uq_users_username UNIQUE (username),
                CONSTRAINT uq_users_slug     UNIQUE (slug)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_users_email    ON users (email)');
        $this->addSql('CREATE INDEX idx_users_username ON users (username)');
        $this->addSql('CREATE INDEX idx_users_slug     ON users (slug)');

        // --- REFRESH TOKENS ---
        $this->addSql("
            CREATE TABLE refresh_tokens (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id     INT UNSIGNED NOT NULL,
                token       VARCHAR(512) NOT NULL,
                revoked     TINYINT(1) NOT NULL DEFAULT 0,
                expires_at  DATETIME NOT NULL,
                created_at  DATETIME NOT NULL,
                CONSTRAINT uq_refresh_tokens_token UNIQUE (token),
                CONSTRAINT fk_rt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_refresh_tokens_user ON refresh_tokens (user_id)');

        // --- PLAYERS ---
        $this->addSql("
            CREATE TABLE players (
                id                  INT UNSIGNED PRIMARY KEY,
                username            VARCHAR(100) NOT NULL,
                elo_rating          DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
                `rank`              INT UNSIGNED NOT NULL DEFAULT 0,
                total_matches       INT UNSIGNED NOT NULL DEFAULT 0,
                wins                INT UNSIGNED NOT NULL DEFAULT 0,
                losses              INT UNSIGNED NOT NULL DEFAULT 0,
                team_elo_rating     DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
                team_rank           INT UNSIGNED NOT NULL DEFAULT 0,
                team_total_matches  INT UNSIGNED NOT NULL DEFAULT 0,
                team_wins           INT UNSIGNED NOT NULL DEFAULT 0,
                team_losses         INT UNSIGNED NOT NULL DEFAULT 0,
                created_at          DATETIME NOT NULL,
                updated_at          DATETIME NOT NULL,
                deleted_at          DATETIME,
                CONSTRAINT fk_players_user FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_players_elo      ON players (elo_rating)');
        $this->addSql('CREATE INDEX idx_players_team_elo ON players (team_elo_rating)');
        $this->addSql('CREATE INDEX idx_players_rank     ON players (`rank`)');

        // --- TOURNAMENTS ---
        $this->addSql("
            CREATE TABLE tournaments (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name            VARCHAR(255) NOT NULL,
                slug            VARCHAR(255) NOT NULL,
                type            ENUM('solo','team') NOT NULL,
                status          ENUM('opened','ongoing','finished') NOT NULL DEFAULT 'opened',
                description     LONGTEXT,
                nb_participants INT UNSIGNED NOT NULL DEFAULT 0,
                nb_matches      INT UNSIGNED NOT NULL DEFAULT 0,
                created_at      DATETIME NOT NULL,
                updated_at      DATETIME NOT NULL,
                deleted_at      DATETIME,
                CONSTRAINT uq_tournaments_slug UNIQUE (slug)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_tournaments_status ON tournaments (status)');
        $this->addSql('CREATE INDEX idx_tournaments_type   ON tournaments (type)');

        // --- TEAMS ---
        $this->addSql("
            CREATE TABLE teams (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                player1_id      INT UNSIGNED NOT NULL,
                player2_id      INT UNSIGNED NOT NULL,
                name            VARCHAR(255) NOT NULL,
                slug            VARCHAR(255) NOT NULL,
                elo_rating      DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
                total_matches   INT UNSIGNED NOT NULL DEFAULT 0,
                wins            INT UNSIGNED NOT NULL DEFAULT 0,
                losses          INT UNSIGNED NOT NULL DEFAULT 0,
                created_at      DATETIME NOT NULL,
                updated_at      DATETIME NOT NULL,
                deleted_at      DATETIME,
                CONSTRAINT uq_teams_slug UNIQUE (slug),
                CONSTRAINT fk_teams_player1 FOREIGN KEY (player1_id) REFERENCES players(id) ON DELETE CASCADE,
                CONSTRAINT fk_teams_player2 FOREIGN KEY (player2_id) REFERENCES players(id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_teams_elo     ON teams (elo_rating)');
        $this->addSql('CREATE INDEX idx_teams_player1 ON teams (player1_id)');
        $this->addSql('CREATE INDEX idx_teams_player2 ON teams (player2_id)');

        // --- MATCHES (solo) ---
        $this->addSql("
            CREATE TABLE matches (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                player1_id      INT UNSIGNED NOT NULL,
                player2_id      INT UNSIGNED NOT NULL,
                winner_id       INT UNSIGNED,
                status          ENUM('pending','confirmed','rejected','cancelled') NOT NULL DEFAULT 'pending',
                tournament_id   INT UNSIGNED,
                confirmed_at    DATETIME,
                created_at      DATETIME NOT NULL,
                updated_at      DATETIME NOT NULL,
                deleted_at      DATETIME,
                CONSTRAINT fk_matches_player1    FOREIGN KEY (player1_id)    REFERENCES players(id) ON DELETE CASCADE,
                CONSTRAINT fk_matches_player2    FOREIGN KEY (player2_id)    REFERENCES players(id) ON DELETE CASCADE,
                CONSTRAINT fk_matches_winner     FOREIGN KEY (winner_id)     REFERENCES players(id) ON DELETE SET NULL,
                CONSTRAINT fk_matches_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_matches_player1    ON matches (player1_id)');
        $this->addSql('CREATE INDEX idx_matches_player2    ON matches (player2_id)');
        $this->addSql('CREATE INDEX idx_matches_winner     ON matches (winner_id)');
        $this->addSql('CREATE INDEX idx_matches_status     ON matches (status)');
        $this->addSql('CREATE INDEX idx_matches_tournament ON matches (tournament_id)');
        $this->addSql('CREATE INDEX idx_matches_created_at ON matches (created_at)');

        // --- TEAM MATCHES ---
        $this->addSql("
            CREATE TABLE team_matches (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                team1_id        INT UNSIGNED NOT NULL,
                team2_id        INT UNSIGNED NOT NULL,
                winner_team_id  INT UNSIGNED,
                status          ENUM('pending','confirmed','rejected','cancelled') NOT NULL DEFAULT 'pending',
                tournament_id   INT UNSIGNED,
                confirmed_at    DATETIME,
                created_at      DATETIME NOT NULL,
                updated_at      DATETIME NOT NULL,
                deleted_at      DATETIME,
                CONSTRAINT fk_tm_team1      FOREIGN KEY (team1_id)       REFERENCES teams(id) ON DELETE CASCADE,
                CONSTRAINT fk_tm_team2      FOREIGN KEY (team2_id)       REFERENCES teams(id) ON DELETE CASCADE,
                CONSTRAINT fk_tm_winner     FOREIGN KEY (winner_team_id) REFERENCES teams(id) ON DELETE SET NULL,
                CONSTRAINT fk_tm_tournament FOREIGN KEY (tournament_id)  REFERENCES tournaments(id) ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_team_matches_team1      ON team_matches (team1_id)');
        $this->addSql('CREATE INDEX idx_team_matches_team2      ON team_matches (team2_id)');
        $this->addSql('CREATE INDEX idx_team_matches_winner     ON team_matches (winner_team_id)');
        $this->addSql('CREATE INDEX idx_team_matches_status     ON team_matches (status)');
        $this->addSql('CREATE INDEX idx_team_matches_tournament ON team_matches (tournament_id)');
        $this->addSql('CREATE INDEX idx_team_matches_created_at ON team_matches (created_at)');

        // --- TOURNAMENT TEAMS (pivot) ---
        $this->addSql("
            CREATE TABLE tournament_teams (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tournament_id   INT UNSIGNED NOT NULL,
                team_id         INT UNSIGNED NOT NULL,
                joined_at       DATETIME NOT NULL,
                CONSTRAINT uq_tournament_team    UNIQUE (tournament_id, team_id),
                CONSTRAINT fk_tt_tournament FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
                CONSTRAINT fk_tt_team       FOREIGN KEY (team_id)       REFERENCES teams(id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        // --- ELO HISTORY (solo) ---
        $this->addSql("
            CREATE TABLE elo_history (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                player_id   INT UNSIGNED NOT NULL,
                match_id    INT UNSIGNED NOT NULL,
                elo_before  DECIMAL(10,2) NOT NULL,
                elo_after   DECIMAL(10,2) NOT NULL,
                elo_change  DECIMAL(10,2) NOT NULL,
                opponent_id INT UNSIGNED,
                created_at  DATETIME NOT NULL,
                updated_at  DATETIME NOT NULL,
                CONSTRAINT fk_eh_player   FOREIGN KEY (player_id)   REFERENCES players(id) ON DELETE CASCADE,
                CONSTRAINT fk_eh_match    FOREIGN KEY (match_id)    REFERENCES matches(id) ON DELETE CASCADE,
                CONSTRAINT fk_eh_opponent FOREIGN KEY (opponent_id) REFERENCES players(id) ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_elo_history_player     ON elo_history (player_id)');
        $this->addSql('CREATE INDEX idx_elo_history_match      ON elo_history (match_id)');
        $this->addSql('CREATE INDEX idx_elo_history_created_at ON elo_history (created_at)');

        // --- TEAM ELO HISTORY ---
        $this->addSql("
            CREATE TABLE team_elo_history (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                player_id       INT UNSIGNED NOT NULL,
                team_match_id   INT UNSIGNED NOT NULL,
                elo_before      DECIMAL(10,2) NOT NULL,
                elo_after       DECIMAL(10,2) NOT NULL,
                elo_change      DECIMAL(10,2) NOT NULL,
                created_at      DATETIME NOT NULL,
                updated_at      DATETIME NOT NULL,
                CONSTRAINT fk_teh_player FOREIGN KEY (player_id)     REFERENCES players(id) ON DELETE CASCADE,
                CONSTRAINT fk_teh_match  FOREIGN KEY (team_match_id) REFERENCES team_matches(id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('CREATE INDEX idx_team_elo_history_player     ON team_elo_history (player_id)');
        $this->addSql('CREATE INDEX idx_team_elo_history_match      ON team_elo_history (team_match_id)');
        $this->addSql('CREATE INDEX idx_team_elo_history_created_at ON team_elo_history (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS team_elo_history');
        $this->addSql('DROP TABLE IF EXISTS elo_history');
        $this->addSql('DROP TABLE IF EXISTS tournament_teams');
        $this->addSql('DROP TABLE IF EXISTS team_matches');
        $this->addSql('DROP TABLE IF EXISTS matches');
        $this->addSql('DROP TABLE IF EXISTS teams');
        $this->addSql('DROP TABLE IF EXISTS tournaments');
        $this->addSql('DROP TABLE IF EXISTS players');
        $this->addSql('DROP TABLE IF EXISTS refresh_tokens');
        $this->addSql('DROP TABLE IF EXISTS users');
    }
}
