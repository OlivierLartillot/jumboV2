<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218075553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE whats_app_message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type_transfer SMALLINT NOT NULL, language VARCHAR(3) NOT NULL, message LONGTEXT NOT NULL, is_default_message TINYINT(1) NOT NULL, INDEX IDX_B62B27F1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE whats_app_message ADD CONSTRAINT FK_B62B27F1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE whats_app_message DROP FOREIGN KEY FK_B62B27F1A76ED395');
        $this->addSql('DROP TABLE whats_app_message');
    }
}
