<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150627143905 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Authorization CHANGE createRegistrationPublicAllowed createRegistrationPublicAllowed SMALLINT NOT NULL, CHANGE changeRegistrationRegisteredAllowed changeRegistrationRegisteredAllowed SMALLINT NOT NULL, CHANGE apiPublicAllowed apiPublicAllowed SMALLINT NOT NULL, CHANGE apiRegisteredAllowed apiRegisteredAllowed SMALLINT NOT NULL, CHANGE liveScorePublicAllowed liveScorePublicAllowed SMALLINT NOT NULL, CHANGE liveScoreRegisteredAllowed liveScoreRegisteredAllowed SMALLINT NOT NULL, CHANGE live2ndCallPublicAllowed live2ndCallPublicAllowed SMALLINT NOT NULL, CHANGE live2ndCallRegisteredAllowed live2ndCallRegisteredAllowed SMALLINT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Authorization CHANGE createRegistrationPublicAllowed createRegistrationPublicAllowed TINYINT(1) NOT NULL, CHANGE changeRegistrationRegisteredAllowed changeRegistrationRegisteredAllowed TINYINT(1) NOT NULL, CHANGE apiPublicAllowed apiPublicAllowed TINYINT(1) NOT NULL, CHANGE apiRegisteredAllowed apiRegisteredAllowed TINYINT(1) NOT NULL, CHANGE liveScorePublicAllowed liveScorePublicAllowed TINYINT(1) NOT NULL, CHANGE liveScoreRegisteredAllowed liveScoreRegisteredAllowed TINYINT(1) NOT NULL, CHANGE live2ndCallPublicAllowed live2ndCallPublicAllowed TINYINT(1) NOT NULL, CHANGE live2ndCallRegisteredAllowed live2ndCallRegisteredAllowed TINYINT(1) NOT NULL');
    }
}
