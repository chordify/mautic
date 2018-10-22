<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181015111952 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE website_notifications_inbox (id INT AUTO_INCREMENT NOT NULL, notification_id INT NOT NULL, contact_id INT NOT NULL, date_sent DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', date_read DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_E45FBFD8EF1A9D84 (notification_id), INDEX IDX_E45FBFD8E7A1254A (contact_id), INDEX unread_search (contact_id, date_read), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE website_notifications (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, translation_parent_id INT DEFAULT NULL, is_published TINYINT(1) NOT NULL, date_added DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_by INT DEFAULT NULL, created_by_user VARCHAR(255) DEFAULT NULL, date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', modified_by INT DEFAULT NULL, modified_by_user VARCHAR(255) DEFAULT NULL, checked_out DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', checked_out_by INT DEFAULT NULL, checked_out_by_user VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, title LONGTEXT NOT NULL, message LONGTEXT NOT NULL, url LONGTEXT DEFAULT NULL, image LONGTEXT DEFAULT NULL, lang VARCHAR(255) NOT NULL, INDEX IDX_4E2092EC12469DE2 (category_id), INDEX IDX_4E2092EC9091A2FB (translation_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE website_notifications_inbox ADD CONSTRAINT FK_E45FBFD8EF1A9D84 FOREIGN KEY (notification_id) REFERENCES website_notifications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE website_notifications_inbox ADD CONSTRAINT FK_E45FBFD8E7A1254A FOREIGN KEY (contact_id) REFERENCES leads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE website_notifications ADD CONSTRAINT FK_4E2092EC12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE website_notifications ADD CONSTRAINT FK_4E2092EC9091A2FB FOREIGN KEY (translation_parent_id) REFERENCES website_notifications (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE website_notifications_inbox DROP FOREIGN KEY FK_E45FBFD8EF1A9D84');
        $this->addSql('ALTER TABLE website_notifications DROP FOREIGN KEY FK_4E2092EC9091A2FB');
        $this->addSql('DROP TABLE website_notifications_inbox');
        $this->addSql('DROP TABLE website_notifications');
    }
}
