<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240211175116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checked_history CHANGE type type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE status_history CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD flight_number VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checked_history CHANGE type type INT DEFAULT NULL COMMENT \'1: checked in airport arrival page, 2: checked in briefing rep page\'');
        $this->addSql('ALTER TABLE status_history CHANGE status_id status_id INT NOT NULL COMMENT \'ceci est un commeantaire\'');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP flight_number');
    }
}
