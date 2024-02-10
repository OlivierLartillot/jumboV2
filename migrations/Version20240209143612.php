<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240209143612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE checked_history (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, updated_by_id INT NOT NULL, is_checked TINYINT(1) DEFAULT NULL, type SMALLINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4602A6856113E9E (customer_card_id), INDEX IDX_4602A68896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql("ALTER TABLE checked_history CHANGE COLUMN type type INT COMMENT '1: checked in airport arrival page, 2: checked in briefing rep page'");
        $this->addSql('ALTER TABLE checked_history ADD CONSTRAINT FK_4602A6856113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE checked_history ADD CONSTRAINT FK_4602A68896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer_card ADD is_checked TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checked_history DROP FOREIGN KEY FK_4602A6856113E9E');
        $this->addSql('ALTER TABLE checked_history DROP FOREIGN KEY FK_4602A68896DBBDE');
        $this->addSql('DROP TABLE checked_history');
        $this->addSql('ALTER TABLE customer_card DROP is_checked');
    }
}
