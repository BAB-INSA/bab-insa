<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Colonnes reprises de l'ancienne base Go (perdues lors de la migration initiale) :
 *  - team_elo_history.opponent_team_id : équipe adverse dans l'historique ELO équipe
 *  - tournament_teams.wins / losses : stats des équipes au sein d'un tournoi
 */
final class Version20260711145614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute team_elo_history.opponent_team_id et tournament_teams.wins/losses (parité API Go)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_elo_history ADD opponent_team_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE team_elo_history ADD CONSTRAINT fk_teh_opponent_team FOREIGN KEY (opponent_team_id) REFERENCES teams (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_team_elo_history_opponent ON team_elo_history (opponent_team_id)');

        $this->addSql('ALTER TABLE tournament_teams ADD wins INT UNSIGNED DEFAULT 0 NOT NULL, ADD losses INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_elo_history DROP FOREIGN KEY fk_teh_opponent_team');
        $this->addSql('DROP INDEX idx_team_elo_history_opponent ON team_elo_history');
        $this->addSql('ALTER TABLE team_elo_history DROP opponent_team_id');

        $this->addSql('ALTER TABLE tournament_teams DROP wins, DROP losses');
    }
}
