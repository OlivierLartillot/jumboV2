<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231105134401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_departure CHANGE vehicle_type vehicle_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel CHANGE vehicle_type vehicle_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival CHANGE vehicle_type vehicle_type VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_departure CHANGE vehicle_type vehicle_type VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel CHANGE vehicle_type vehicle_type VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival CHANGE vehicle_type vehicle_type VARCHAR(10) DEFAULT NULL');
    }
}
