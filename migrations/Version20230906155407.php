<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230906155407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival CHANGE service_number service_number VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE transfer_departure CHANGE service_number service_number VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel CHANGE service_number service_number VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival CHANGE service_number service_number SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE transfer_departure CHANGE service_number service_number SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel CHANGE service_number service_number SMALLINT NOT NULL');
    }
}
