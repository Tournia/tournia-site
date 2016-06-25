<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150621183752 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DA33D1A3E7');
        $this->addSql('DROP INDEX UNIQ_C971A6DA33D1A3E7 ON Site');
        $this->addSql('ALTER TABLE Site DROP tournament_id');
        $this->addSql('ALTER TABLE Tournament ADD site_id INT NOT NULL');
        $this->addSql('ALTER TABLE Tournament ADD CONSTRAINT FK_F202BB09F6BD1646 FOREIGN KEY (site_id) REFERENCES Site (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F202BB09F6BD1646 ON Tournament (site_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site ADD tournament_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DA33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES Tournament (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C971A6DA33D1A3E7 ON Site (tournament_id)');
        $this->addSql('ALTER TABLE Tournament DROP FOREIGN KEY FK_F202BB09F6BD1646');
        $this->addSql('DROP INDEX UNIQ_F202BB09F6BD1646 ON Tournament');
        $this->addSql('ALTER TABLE Tournament DROP site_id');
    }
}
