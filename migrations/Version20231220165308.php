<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220165308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting_point ADD whats_app_en VARCHAR(255) DEFAULT NULL, ADD whats_app_es VARCHAR(255) DEFAULT NULL, ADD whats_app_fr VARCHAR(255) DEFAULT NULL, ADD whats_app_it VARCHAR(255) DEFAULT NULL, ADD whats_app_po VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting_point DROP whats_app_en, DROP whats_app_es, DROP whats_app_fr, DROP whats_app_it, DROP whats_app_po');
    }
}
