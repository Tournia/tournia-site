<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150321202320 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE OauthAuthCode (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, loginAccount_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_25E7D48A5F37A13B (token), INDEX IDX_25E7D48A19EB6921 (client_id), INDEX IDX_25E7D48AF934D782 (loginAccount_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE OauthClient (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE OauthRefreshToken (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, loginAccount_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5D7D1B315F37A13B (token), INDEX IDX_5D7D1B3119EB6921 (client_id), INDEX IDX_5D7D1B31F934D782 (loginAccount_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE OauthAuthCode ADD CONSTRAINT FK_25E7D48A19EB6921 FOREIGN KEY (client_id) REFERENCES OauthClient (id)');
        $this->addSql('ALTER TABLE OauthAuthCode ADD CONSTRAINT FK_25E7D48AF934D782 FOREIGN KEY (loginAccount_id) REFERENCES LoginAccount (id)');
        $this->addSql('ALTER TABLE OauthRefreshToken ADD CONSTRAINT FK_5D7D1B3119EB6921 FOREIGN KEY (client_id) REFERENCES OauthClient (id)');
        $this->addSql('ALTER TABLE OauthRefreshToken ADD CONSTRAINT FK_5D7D1B31F934D782 FOREIGN KEY (loginAccount_id) REFERENCES LoginAccount (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE OauthAuthCode DROP FOREIGN KEY FK_25E7D48A19EB6921');
        $this->addSql('ALTER TABLE OauthRefreshToken DROP FOREIGN KEY FK_5D7D1B3119EB6921');
        $this->addSql('DROP TABLE OauthAuthCode');
        $this->addSql('DROP TABLE OauthClient');
        $this->addSql('DROP TABLE OauthRefreshToken');
    }
}
