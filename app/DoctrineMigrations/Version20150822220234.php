<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150822220234 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE NotificationLog (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, tournament_id INT DEFAULT NULL, type VARCHAR(32) NOT NULL, deviceToken VARCHAR(255) NOT NULL, platform VARCHAR(10) NOT NULL, message LONGTEXT NOT NULL, datetime DATETIME NOT NULL, INDEX IDX_B6EC480F217BBB47 (person_id), INDEX IDX_B6EC480F33D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE NotificationLog ADD CONSTRAINT FK_B6EC480F217BBB47 FOREIGN KEY (person_id) REFERENCES Person (id)');
        $this->addSql('ALTER TABLE NotificationLog ADD CONSTRAINT FK_B6EC480F33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES Tournament (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE NotificationLog');
    }
}
