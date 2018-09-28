<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180928071208 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX stat_devices_search ON email_stats (email_id, date_read, list_id)');
        $this->addSql('CREATE INDEX stat_sent_count ON email_stats (list_id, email_id, is_failed, date_sent)');
        $this->addSql('CREATE INDEX stat_email_hit_count ON email_stats (email_id, source)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX stat_devices_search ON email_stats');
        $this->addSql('DROP INDEX stat_sent_count ON email_stats');
        $this->addSql('DROP INDEX stat_email_hit_count ON email_stats');
    }
}
