<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150703193308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Tournament ADD financialMethod VARCHAR(32) NOT NULL');
        $this->addSql('UPDATE Tournament SET  `financialMethod` =  \'free\' WHERE  financialEnabled=0;');
        $this->addSql('UPDATE Tournament SET  `financialMethod` =  \'payments\' WHERE  financialEnabled=1;');
        $this->addSql('ALTER TABLE Tournament DROP financialEnabled');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Tournament ADD financialEnabled TINYINT(1) NOT NULL, DROP financialMethod');
    }
}
