<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151024125300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE DisciplinePlayer (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, discipline_id INT DEFAULT NULL, partner VARCHAR(255) DEFAULT NULL, INDEX IDX_45D55BFA99E6F5DF (player_id), INDEX IDX_45D55BFAA5522701 (discipline_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE DisciplineType (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, partnerRegistration TINYINT(1) NOT NULL, INDEX IDX_B095931733D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE DisciplinePlayer ADD CONSTRAINT FK_45D55BFA99E6F5DF FOREIGN KEY (player_id) REFERENCES Player (id)');
        $this->addSql('ALTER TABLE DisciplinePlayer ADD CONSTRAINT FK_45D55BFAA5522701 FOREIGN KEY (discipline_id) REFERENCES Discipline (id)');
        $this->addSql('ALTER TABLE DisciplineType ADD CONSTRAINT FK_B095931733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES Tournament (id)');
        $this->addSql('ALTER TABLE Discipline ADD disciplineType_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Discipline ADD CONSTRAINT FK_3AE3EDEF8B82FC9D FOREIGN KEY (disciplineType_id) REFERENCES DisciplineType (id)');
        $this->addSql('CREATE INDEX IDX_3AE3EDEF8B82FC9D ON Discipline (disciplineType_id)');
        $this->addSql('ALTER TABLE Player DROP FOREIGN KEY FK_9FB57F53496B3B0D');
        $this->addSql('ALTER TABLE Player DROP FOREIGN KEY FK_9FB57F536ECABF9F');
        $this->addSql('ALTER TABLE Player DROP FOREIGN KEY FK_9FB57F53D54E7AC0');
        $this->addSql('DROP INDEX IDX_9FB57F536ECABF9F ON Player');
        $this->addSql('DROP INDEX IDX_9FB57F53D54E7AC0 ON Player');
        $this->addSql('DROP INDEX IDX_9FB57F53496B3B0D ON Player');
        $this->addSql('ALTER TABLE Player DROP partnerDoubles, DROP partnerMixed, DROP singles_disciplineId, DROP doubles_disciplineId, DROP mixed_disciplineId');
        $this->addSql('ALTER TABLE Tournament DROP partnerRegistration');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Discipline DROP FOREIGN KEY FK_3AE3EDEF8B82FC9D');
        $this->addSql('DROP TABLE DisciplinePlayer');
        $this->addSql('DROP TABLE DisciplineType');
        $this->addSql('DROP INDEX IDX_3AE3EDEF8B82FC9D ON Discipline');
        $this->addSql('ALTER TABLE Discipline DROP disciplineType_id');
        $this->addSql('ALTER TABLE Player ADD partnerDoubles VARCHAR(255) DEFAULT NULL, ADD partnerMixed VARCHAR(255) DEFAULT NULL, ADD singles_disciplineId INT DEFAULT NULL, ADD doubles_disciplineId INT DEFAULT NULL, ADD mixed_disciplineId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Player ADD CONSTRAINT FK_9FB57F53496B3B0D FOREIGN KEY (mixed_disciplineId) REFERENCES Discipline (id)');
        $this->addSql('ALTER TABLE Player ADD CONSTRAINT FK_9FB57F536ECABF9F FOREIGN KEY (singles_disciplineId) REFERENCES Discipline (id)');
        $this->addSql('ALTER TABLE Player ADD CONSTRAINT FK_9FB57F53D54E7AC0 FOREIGN KEY (doubles_disciplineId) REFERENCES Discipline (id)');
        $this->addSql('CREATE INDEX IDX_9FB57F536ECABF9F ON Player (singles_disciplineId)');
        $this->addSql('CREATE INDEX IDX_9FB57F53D54E7AC0 ON Player (doubles_disciplineId)');
        $this->addSql('CREATE INDEX IDX_9FB57F53496B3B0D ON Player (mixed_disciplineId)');
        $this->addSql('ALTER TABLE Tournament ADD partnerRegistration TINYINT(1) NOT NULL');
    }
}
