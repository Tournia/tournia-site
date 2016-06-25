<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150321205600 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE OauthAccessToken (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, loginAccount_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5524334B5F37A13B (token), INDEX IDX_5524334B19EB6921 (client_id), INDEX IDX_5524334BF934D782 (loginAccount_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE OauthAccessToken ADD CONSTRAINT FK_5524334B19EB6921 FOREIGN KEY (client_id) REFERENCES OauthClient (id)');
        $this->addSql('ALTER TABLE OauthAccessToken ADD CONSTRAINT FK_5524334BF934D782 FOREIGN KEY (loginAccount_id) REFERENCES LoginAccount (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE OauthAccessToken');
    }
}
