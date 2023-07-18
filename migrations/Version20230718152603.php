<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230718152603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD reservation_number VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD reservation_number VARCHAR(30) DEFAULT NULL, CHANGE customer_card_id customer_card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD reservation_number VARCHAR(30) DEFAULT NULL, CHANGE customer_card_id customer_card_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP date, DROP reservation_number');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP date, DROP reservation_number, CHANGE customer_card_id customer_card_id INT NOT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP date, DROP reservation_number, CHANGE customer_card_id customer_card_id INT NOT NULL');
    }
}
