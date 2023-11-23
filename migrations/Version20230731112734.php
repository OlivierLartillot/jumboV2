<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230731112734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE airport_hotel (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_airport TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_arrival (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, service_number SMALLINT NOT NULL, date_hour DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(10) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_E4502EFF56113E9E (customer_card_id), INDEX IDX_E4502EFF24D21901 (from_start_id), INDEX IDX_E4502EFF454B7575 (to_arrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_departure (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, service_number SMALLINT NOT NULL, date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(10) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_E86FFF1A56113E9E (customer_card_id), INDEX IDX_E86FFF1A24D21901 (from_start_id), INDEX IDX_E86FFF1A454B7575 (to_arrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_inter_hotel (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, service_number SMALLINT NOT NULL, date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(10) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_2026549256113E9E (customer_card_id), INDEX IDX_2026549224D21901 (from_start_id), INDEX IDX_20265492454B7575 (to_arrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_vehicle_arrival (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT DEFAULT NULL, is_collective TINYINT(1) NOT NULL, vehicle_number SMALLINT DEFAULT NULL, vehicle_type VARCHAR(10) DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', pick_up VARCHAR(6) DEFAULT NULL, transport_company VARCHAR(100) DEFAULT NULL, voucher_number VARCHAR(16) DEFAULT NULL, area VARCHAR(20) DEFAULT NULL, reservation_number VARCHAR(30) DEFAULT NULL, UNIQUE INDEX UNIQ_9306036156113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_vehicle_departure (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, is_collective TINYINT(1) NOT NULL, vehicle_number SMALLINT DEFAULT NULL, vehicle_type VARCHAR(10) DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', pick_up VARCHAR(6) DEFAULT NULL, transport_company VARCHAR(100) DEFAULT NULL, voucher_number VARCHAR(16) DEFAULT NULL, area VARCHAR(20) DEFAULT NULL, reservation_number VARCHAR(30) DEFAULT NULL, UNIQUE INDEX UNIQ_427273AD56113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_vehicle_inter_hotel (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT DEFAULT NULL, is_collective TINYINT(1) NOT NULL, vehicle_number SMALLINT DEFAULT NULL, vehicle_type VARCHAR(10) DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', pick_up VARCHAR(6) DEFAULT NULL, transport_company VARCHAR(100) DEFAULT NULL, voucher_number VARCHAR(16) DEFAULT NULL, area VARCHAR(20) DEFAULT NULL, reservation_number VARCHAR(30) DEFAULT NULL, INDEX IDX_6FAC09FB56113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF24D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1A56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1A24D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1A454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_2026549256113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_2026549224D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_9306036156113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD CONSTRAINT FK_427273AD56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD CONSTRAINT FK_6FAC09FB56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('DROP TABLE drag_and_drop');
        $this->addSql('ALTER TABLE customer_card ADD agency_id INT DEFAULT NULL, DROP agency');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA4CDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id)');
        $this->addSql('CREATE INDEX IDX_42853CA4CDEADB2A ON customer_card (agency_id)');
        $this->addSql('ALTER TABLE transfer DROP service_number, DROP nature_transfer, DROP date_hour, DROP flight_number, DROP private_collective, DROP adults_number, DROP children_number, DROP babies_number, DROP from_start_id, DROP to_arrival_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA4CDEADB2A');
        $this->addSql('CREATE TABLE drag_and_drop (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF56113E9E');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF24D21901');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF454B7575');
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1A56113E9E');
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1A24D21901');
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1A454B7575');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_2026549256113E9E');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_2026549224D21901');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492454B7575');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_9306036156113E9E');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP FOREIGN KEY FK_427273AD56113E9E');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP FOREIGN KEY FK_6FAC09FB56113E9E');
        $this->addSql('DROP TABLE agency');
        $this->addSql('DROP TABLE airport_hotel');
        $this->addSql('DROP TABLE transfer_arrival');
        $this->addSql('DROP TABLE transfer_departure');
        $this->addSql('DROP TABLE transfer_inter_hotel');
        $this->addSql('DROP TABLE transfer_vehicle_arrival');
        $this->addSql('DROP TABLE transfer_vehicle_departure');
        $this->addSql('DROP TABLE transfer_vehicle_inter_hotel');
        $this->addSql('DROP INDEX IDX_42853CA4CDEADB2A ON customer_card');
        $this->addSql('ALTER TABLE customer_card ADD agency VARCHAR(255) NOT NULL, DROP agency_id');
        $this->addSql('ALTER TABLE transfer ADD service_number VARCHAR(24) DEFAULT NULL, ADD nature_transfer VARCHAR(24) NOT NULL, ADD date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD flight_number VARCHAR(24) DEFAULT NULL, ADD private_collective VARCHAR(24) DEFAULT NULL, ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL, ADD from_start_id INT NOT NULL, ADD to_arrival_id INT NOT NULL');
    }
}
