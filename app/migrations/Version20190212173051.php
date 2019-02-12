<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20190212173051 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE campaign_leads ADD form_submission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE campaign_leads ADD CONSTRAINT FK_5995213D422B0E0C FOREIGN KEY (form_submission_id) REFERENCES form_submissions (id)');
        $this->addSql('CREATE INDEX IDX_5995213D422B0E0C ON campaign_leads (form_submission_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE campaign_leads DROP FOREIGN KEY FK_5995213D422B0E0C');
        $this->addSql('DROP INDEX IDX_5995213D422B0E0C ON campaign_leads');
        $this->addSql('ALTER TABLE campaign_leads DROP form_submission_id');
    }
}
