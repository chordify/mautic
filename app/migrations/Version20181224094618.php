<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20181224094618 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX sent_stats ON website_notifications_inbox (notification_id, date_sent)');
        $this->addSql('CREATE INDEX read_stats ON website_notifications_inbox (notification_id, date_read)');
        $this->addSql('CREATE INDEX hidden_stats ON website_notifications_inbox (notification_id, date_sent)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX sent_stats ON website_notifications_inbox');
        $this->addSql('DROP INDEX read_stats ON website_notifications_inbox');
        $this->addSql('DROP INDEX hidden_stats ON website_notifications_inbox');
    }
}
