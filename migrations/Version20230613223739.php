<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613223739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_predefined_comments_messages (comment_id INT NOT NULL, predefined_comments_messages_id INT NOT NULL, INDEX IDX_315BDE4AF8697D13 (comment_id), INDEX IDX_315BDE4ADEA22706 (predefined_comments_messages_id), PRIMARY KEY(comment_id, predefined_comments_messages_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment_predefined_comments_messages ADD CONSTRAINT FK_315BDE4AF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_predefined_comments_messages ADD CONSTRAINT FK_315BDE4ADEA22706 FOREIGN KEY (predefined_comments_messages_id) REFERENCES predefined_comments_messages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment DROP predefined_messages');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment_predefined_comments_messages DROP FOREIGN KEY FK_315BDE4AF8697D13');
        $this->addSql('ALTER TABLE comment_predefined_comments_messages DROP FOREIGN KEY FK_315BDE4ADEA22706');
        $this->addSql('DROP TABLE comment_predefined_comments_messages');
        $this->addSql('ALTER TABLE comment ADD predefined_messages VARCHAR(255) DEFAULT NULL');
    }
}
