<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151111200752 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pool_discipline (pool_id INT NOT NULL, discipline_id INT NOT NULL, INDEX IDX_726F63DD7B3406DF (pool_id), INDEX IDX_726F63DDA5522701 (discipline_id), PRIMARY KEY(pool_id, discipline_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pool_discipline ADD CONSTRAINT FK_726F63DD7B3406DF FOREIGN KEY (pool_id) REFERENCES Pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pool_discipline ADD CONSTRAINT FK_726F63DDA5522701 FOREIGN KEY (discipline_id) REFERENCES Discipline (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Pool CHANGE name name VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pool_discipline');
        $this->addSql('ALTER TABLE Pool CHANGE name name VARCHAR(255) DEFAULT NULL');
    }
}
