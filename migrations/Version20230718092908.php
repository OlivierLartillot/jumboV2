<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230718092908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival CHANGE pick_up pick_up VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_departure CHANGE pick_up pick_up VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel CHANGE pick_up pick_up VARCHAR(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival CHANGE pick_up pick_up TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_vehicle_departure CHANGE pick_up pick_up TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel CHANGE pick_up pick_up TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }
}
