<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150815134406 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE NotificationSubscription (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, deviceToken VARCHAR(255) NOT NULL, platform VARCHAR(10) NOT NULL, enabled TINYINT(1) NOT NULL, upcomingMatchPeriod INT NOT NULL, newMatchEnabled TINYINT(1) NOT NULL, scoreMatchEnabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CE07C0EE4AEA2B1E (deviceToken), INDEX IDX_CE07C0EE217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE NotificationSubscription ADD CONSTRAINT FK_CE07C0EE217BBB47 FOREIGN KEY (person_id) REFERENCES Person (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE NotificationSubscription');
    }
}
