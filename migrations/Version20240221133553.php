<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221133553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_card ADD client_language VARCHAR(24) DEFAULT NULL, CHANGE jumbo_number jumbo_number VARCHAR(24) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_arrival CHANGE service_number service_number VARCHAR(50) DEFAULT NULL, CHANGE hour hour TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE user ADD usage_name VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_card DROP client_language, CHANGE jumbo_number jumbo_number VARCHAR(24) NOT NULL');
        $this->addSql('ALTER TABLE transfer_arrival CHANGE service_number service_number VARCHAR(50) NOT NULL, CHANGE hour hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE `user` DROP usage_name');
    }
}
