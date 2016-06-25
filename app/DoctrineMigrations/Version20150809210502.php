<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150809210502 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DAADA85231');
        $this->addSql('DROP INDEX IDX_C971A6DAADA85231 ON Site');
        $this->addSql('ALTER TABLE Site CHANGE headerbackground_id headerBackgroundImage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DA5719523C FOREIGN KEY (headerBackgroundImage_id) REFERENCES File (id)');
        $this->addSql('CREATE INDEX IDX_C971A6DA5719523C ON Site (headerBackgroundImage_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DA5719523C');
        $this->addSql('DROP INDEX IDX_C971A6DA5719523C ON Site');
        $this->addSql('ALTER TABLE Site CHANGE headerbackgroundimage_id headerBackground_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DAADA85231 FOREIGN KEY (headerBackground_id) REFERENCES File (id)');
        $this->addSql('CREATE INDEX IDX_C971A6DAADA85231 ON Site (headerBackground_id)');
    }
}
