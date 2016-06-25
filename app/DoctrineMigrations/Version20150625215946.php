<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150625215946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Authorization ADD createRegistrationPublicStart DATETIME DEFAULT NULL, ADD createRegistrationPublicEnd DATETIME DEFAULT NULL, ADD createRegistrationRegisteredAllowed TINYINT(1) NOT NULL, ADD createRegistrationRegisteredStart DATETIME DEFAULT NULL, ADD createRegistrationRegisteredEnd DATETIME DEFAULT NULL, ADD changeRegistrationRegisteredAllowed TINYINT(1) NOT NULL, ADD changeRegistrationRegisteredStart DATETIME DEFAULT NULL, ADD changeRegistrationRegisteredEnd DATETIME DEFAULT NULL, ADD apiPublicAllowed TINYINT(1) NOT NULL, ADD apiPublicStart DATETIME DEFAULT NULL, ADD apiPublicEnd DATETIME DEFAULT NULL, ADD apiRegisteredAllowed TINYINT(1) NOT NULL, ADD apiRegisteredStart DATETIME DEFAULT NULL, ADD apiRegisteredEnd DATETIME DEFAULT NULL, ADD liveScorePublicAllowed TINYINT(1) NOT NULL, ADD liveScoreRegisteredAllowed TINYINT(1) NOT NULL, ADD live2ndCallPublicAllowed TINYINT(1) NOT NULL, ADD live2ndCallRegisteredAllowed TINYINT(1) NOT NULL, DROP startDateTime, DROP endDateTime');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Authorization ADD startDateTime DATETIME DEFAULT NULL, ADD endDateTime DATETIME DEFAULT NULL, DROP createRegistrationPublicStart, DROP createRegistrationPublicEnd, DROP createRegistrationRegisteredAllowed, DROP createRegistrationRegisteredStart, DROP createRegistrationRegisteredEnd, DROP changeRegistrationRegisteredAllowed, DROP changeRegistrationRegisteredStart, DROP changeRegistrationRegisteredEnd, DROP apiPublicAllowed, DROP apiPublicStart, DROP apiPublicEnd, DROP apiRegisteredAllowed, DROP apiRegisteredStart, DROP apiRegisteredEnd, DROP liveScorePublicAllowed, DROP liveScoreRegisteredAllowed, DROP live2ndCallPublicAllowed, DROP live2ndCallRegisteredAllowed');
    }
}
