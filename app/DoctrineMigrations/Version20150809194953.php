<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150809194953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DAE40AC43');
        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DA69AF00DC');
        $this->addSql('DROP TABLE ImageDimensions');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DAE40AC43 FOREIGN KEY (infoBlockImage_id) REFERENCES File (id)');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DA69AF00DC FOREIGN KEY (frontImage_id) REFERENCES File (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ImageDimensions (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, x INT DEFAULT NULL, y INT DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, INDEX IDX_B0FBCCA293CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ImageDimensions ADD CONSTRAINT FK_B0FBCCA293CB796C FOREIGN KEY (file_id) REFERENCES File (id)');
        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DAE40AC43');
        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DA69AF00DC');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DAE40AC43 FOREIGN KEY (infoBlockImage_id) REFERENCES ImageDimensions (id)');
        $this->addSql('ALTER TABLE Site ADD CONSTRAINT FK_C971A6DA69AF00DC FOREIGN KEY (frontImage_id) REFERENCES ImageDimensions (id)');
    }
}
