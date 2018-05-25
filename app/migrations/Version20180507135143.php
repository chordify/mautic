<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180507135143 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE push_notifications ADD translation_parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE push_notifications ADD CONSTRAINT FK_5B9B7E4F9091A2FB FOREIGN KEY (translation_parent_id) REFERENCES push_notifications (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5B9B7E4F9091A2FB ON push_notifications (translation_parent_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE push_notifications DROP FOREIGN KEY FK_5B9B7E4F9091A2FB');
        $this->addSql('DROP INDEX IDX_5B9B7E4F9091A2FB ON push_notifications');
        $this->addSql('ALTER TABLE push_notifications DROP translation_parent_id');
    }
}
