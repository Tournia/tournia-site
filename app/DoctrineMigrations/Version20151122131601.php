<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151122131601 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Pool ADD nrPlayersInTeam INT NOT NULL');
        $this->addSql('INSERT INTO Pool (id, tournament_id, name, position, algorithm, nrPlayersInTeam) SELECT id, tournament_id, name, position, algorithm, nrPlayersInTeam FROM `Discipline`');
        $this->addSql('INSERT INTO pool_discipline (pool_id, discipline_id) SELECT id, id FROM `Discipline`');
        $this->addSql('DROP TABLE discipline_player');
        $this->addSql('ALTER TABLE Discipline DROP nrPlayersInTeam, DROP algorithm');
        $this->addSql('ALTER TABLE Matchh DROP FOREIGN KEY FK_E6D7AC9BA5522701');
        $this->addSql('DROP INDEX IDX_E6D7AC9BA5522701 ON Matchh');
        $this->addSql('ALTER TABLE Matchh CHANGE discipline_id pool_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Matchh ADD CONSTRAINT FK_E6D7AC9B7B3406DF FOREIGN KEY (pool_id) REFERENCES Pool (id)');
        $this->addSql('CREATE INDEX IDX_E6D7AC9B7B3406DF ON Matchh (pool_id)');
        $this->addSql('ALTER TABLE Team DROP FOREIGN KEY FK_64D20921A5522701');
        $this->addSql('DROP INDEX IDX_64D20921A5522701 ON Team');
        $this->addSql('ALTER TABLE Team CHANGE discipline_id pool_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Team ADD CONSTRAINT FK_64D209217B3406DF FOREIGN KEY (pool_id) REFERENCES Pool (id)');
        $this->addSql('CREATE INDEX IDX_64D209217B3406DF ON Team (pool_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discipline_player (discipline_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_E059D474A5522701 (discipline_id), INDEX IDX_E059D47499E6F5DF (player_id), PRIMARY KEY(discipline_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discipline_player ADD CONSTRAINT FK_E059D47499E6F5DF FOREIGN KEY (player_id) REFERENCES Player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discipline_player ADD CONSTRAINT FK_E059D474A5522701 FOREIGN KEY (discipline_id) REFERENCES Discipline (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Discipline ADD nrPlayersInTeam INT NOT NULL, ADD algorithm VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE Matchh DROP FOREIGN KEY FK_E6D7AC9B7B3406DF');
        $this->addSql('DROP INDEX IDX_E6D7AC9B7B3406DF ON Matchh');
        $this->addSql('ALTER TABLE Matchh CHANGE pool_id discipline_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Matchh ADD CONSTRAINT FK_E6D7AC9BA5522701 FOREIGN KEY (discipline_id) REFERENCES Discipline (id)');
        $this->addSql('CREATE INDEX IDX_E6D7AC9BA5522701 ON Matchh (discipline_id)');
        $this->addSql('ALTER TABLE Pool DROP nrPlayersInTeam');
        $this->addSql('ALTER TABLE Team DROP FOREIGN KEY FK_64D209217B3406DF');
        $this->addSql('DROP INDEX IDX_64D209217B3406DF ON Team');
        $this->addSql('ALTER TABLE Team CHANGE pool_id discipline_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Team ADD CONSTRAINT FK_64D20921A5522701 FOREIGN KEY (discipline_id) REFERENCES Discipline (id)');
        $this->addSql('CREATE INDEX IDX_64D20921A5522701 ON Team (discipline_id)');
    }
}
