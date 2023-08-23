<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823104837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE printing_options_agency (printing_options_id INT NOT NULL, agency_id INT NOT NULL, INDEX IDX_121565BC5189E7C7 (printing_options_id), INDEX IDX_121565BCCDEADB2A (agency_id), PRIMARY KEY(printing_options_id, agency_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE printing_options_agency ADD CONSTRAINT FK_121565BC5189E7C7 FOREIGN KEY (printing_options_id) REFERENCES printing_options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE printing_options_agency ADD CONSTRAINT FK_121565BCCDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE printing_options_agency DROP FOREIGN KEY FK_121565BC5189E7C7');
        $this->addSql('ALTER TABLE printing_options_agency DROP FOREIGN KEY FK_121565BCCDEADB2A');
        $this->addSql('DROP TABLE printing_options_agency');
    }
}
