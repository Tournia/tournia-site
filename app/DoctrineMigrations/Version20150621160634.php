<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150621160634 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Authorization (id INT AUTO_INCREMENT NOT NULL, createRegistrationPublicAllowed TINYINT(1) NOT NULL, startDateTime DATETIME DEFAULT NULL, endDateTime DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Tournament ADD authorization_id INT NOT NULL');
        $this->addSql('ALTER TABLE Tournament ADD CONSTRAINT FK_F202BB092F8B0EB2 FOREIGN KEY (authorization_id) REFERENCES Authorization (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F202BB092F8B0EB2 ON Tournament (authorization_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Tournament DROP FOREIGN KEY FK_F202BB092F8B0EB2');
        $this->addSql('DROP TABLE Authorization');
        $this->addSql('DROP INDEX UNIQ_F202BB092F8B0EB2 ON Tournament');
        $this->addSql('ALTER TABLE Tournament DROP authorization_id');
    }
}
