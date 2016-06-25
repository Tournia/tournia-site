<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150705130138 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `createRegistrationPublicChoice`  `createRegistrationChoice` SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `createRegistrationPublicStart`  `createRegistrationStart` DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `createRegistrationPublicEnd`  `createRegistrationEnd` DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `changeRegistrationRegisteredChoice`  `changeRegistrationChoice` SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `changeRegistrationRegisteredStart`  `changeRegistrationStart` DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `changeRegistrationRegisteredEnd`  `changeRegistrationEnd` DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `apiPublicChoice`  `apiChoice` SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `apiPublicStart`  `apiStart` DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `apiPublicEnd`  `apiEnd` DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `liveScorePublicChoice`  `liveScoreChoice` SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` CHANGE  `live2ndCallPublicChoice`  `live2ndCallChoice` SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` ADD  `apiPubliclyAccessible` TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` ADD  `liveScorePubliclyAccessible` TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` ADD  `live2ndCallPubliclyAccessible` TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE  `Authorization` DROP `apiRegisteredChoice`');
        $this->addSql('ALTER TABLE  `Authorization` DROP `apiRegisteredStart`');
        $this->addSql('ALTER TABLE  `Authorization` DROP `apiRegisteredEnd`');
        $this->addSql('ALTER TABLE  `Authorization` DROP `liveScoreRegisteredChoice`');
        $this->addSql('ALTER TABLE  `Authorization` DROP `live2ndCallRegisteredChoice`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Authorization ADD createRegistrationPublicStart DATETIME DEFAULT NULL, ADD createRegistrationPublicEnd DATETIME DEFAULT NULL, ADD changeRegistrationRegisteredStart DATETIME DEFAULT NULL, ADD changeRegistrationRegisteredEnd DATETIME DEFAULT NULL, ADD apiPublicStart DATETIME DEFAULT NULL, ADD apiPublicEnd DATETIME DEFAULT NULL, ADD apiRegisteredStart DATETIME DEFAULT NULL, ADD apiRegisteredEnd DATETIME DEFAULT NULL, ADD createRegistrationPublicChoice SMALLINT NOT NULL, ADD changeRegistrationRegisteredChoice SMALLINT NOT NULL, ADD apiPublicChoice SMALLINT NOT NULL, ADD apiRegisteredChoice SMALLINT NOT NULL, ADD liveScorePublicChoice SMALLINT NOT NULL, ADD liveScoreRegisteredChoice SMALLINT NOT NULL, ADD live2ndCallPublicChoice SMALLINT NOT NULL, ADD live2ndCallRegisteredChoice SMALLINT NOT NULL, DROP createRegistrationChoice, DROP createRegistrationStart, DROP createRegistrationEnd, DROP changeRegistrationChoice, DROP changeRegistrationStart, DROP changeRegistrationEnd, DROP apiChoice, DROP apiStart, DROP apiEnd, DROP apiPubliclyAccessible, DROP liveScoreChoice, DROP liveScorePubliclyAccessible, DROP live2ndCallChoice, DROP live2ndCallPubliclyAccessible');
    }
}
