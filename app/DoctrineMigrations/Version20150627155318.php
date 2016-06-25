<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150627155318 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Site DROP isRegistrationClosed, DROP isLiveClosed');
        $this->addSql('ALTER TABLE Authorization ADD createRegistrationPublicChoice SMALLINT NOT NULL, ADD changeRegistrationRegisteredChoice SMALLINT NOT NULL, ADD apiPublicChoice SMALLINT NOT NULL, ADD apiRegisteredChoice SMALLINT NOT NULL, ADD liveScorePublicChoice SMALLINT NOT NULL, ADD liveScoreRegisteredChoice SMALLINT NOT NULL, ADD live2ndCallPublicChoice SMALLINT NOT NULL, ADD live2ndCallRegisteredChoice SMALLINT NOT NULL, DROP createRegistrationPublicAllowed, DROP createRegistrationRegisteredAllowed, DROP createRegistrationRegisteredStart, DROP createRegistrationRegisteredEnd, DROP changeRegistrationRegisteredAllowed, DROP apiPublicAllowed, DROP apiRegisteredAllowed, DROP liveScorePublicAllowed, DROP liveScoreRegisteredAllowed, DROP live2ndCallPublicAllowed, DROP live2ndCallRegisteredAllowed');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Authorization ADD createRegistrationPublicAllowed SMALLINT NOT NULL, ADD createRegistrationRegisteredAllowed TINYINT(1) NOT NULL, ADD createRegistrationRegisteredStart DATETIME DEFAULT NULL, ADD createRegistrationRegisteredEnd DATETIME DEFAULT NULL, ADD changeRegistrationRegisteredAllowed SMALLINT NOT NULL, ADD apiPublicAllowed SMALLINT NOT NULL, ADD apiRegisteredAllowed SMALLINT NOT NULL, ADD liveScorePublicAllowed SMALLINT NOT NULL, ADD liveScoreRegisteredAllowed SMALLINT NOT NULL, ADD live2ndCallPublicAllowed SMALLINT NOT NULL, ADD live2ndCallRegisteredAllowed SMALLINT NOT NULL, DROP createRegistrationPublicChoice, DROP changeRegistrationRegisteredChoice, DROP apiPublicChoice, DROP apiRegisteredChoice, DROP liveScorePublicChoice, DROP liveScoreRegisteredChoice, DROP live2ndCallPublicChoice, DROP live2ndCallRegisteredChoice');
        $this->addSql('ALTER TABLE Site ADD isRegistrationClosed TINYINT(1) NOT NULL, ADD isLiveClosed TINYINT(1) NOT NULL');
    }
}
