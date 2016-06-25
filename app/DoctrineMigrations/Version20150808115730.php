<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150808115730 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ImageDimensions (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, x INT NOT NULL, y INT NOT NULL, width INT NOT NULL, height INT NOT NULL, INDEX IDX_B0FBCCA293CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ImageDimensions ADD CONSTRAINT FK_B0FBCCA293CB796C FOREIGN KEY (file_id) REFERENCES File (id)');
        $this->addSql('ALTER TABLE Site ADD frontImage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DA69AF00DC FOREIGN KEY (frontImage_id) REFERENCES ImageDimensions (id)');
        $this->addSql('CREATE INDEX IDX_C971A6DA69AF00DC ON Site (frontImage_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DA69AF00DC');
        $this->addSql('DROP TABLE ImageDimensions');
        $this->addSql('DROP INDEX IDX_C971A6DA69AF00DC ON Site');
        $this->addSql('ALTER TABLE Site DROP frontImage_id');
    }
}
