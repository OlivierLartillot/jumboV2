<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613103145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer_joan (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_joan VARCHAR(255) NOT NULL, to_arrival_joan VARCHAR(255) NOT NULL, date_hour_joan DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number_joan VARCHAR(24) DEFAULT NULL, private_collective_joan VARCHAR(24) DEFAULT NULL, pickup_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', transport_company VARCHAR(60) NOT NULL, vehicle_number SMALLINT NOT NULL, vehicle_type VARCHAR(24) NOT NULL, transfer_area VARCHAR(24) NOT NULL, voucher_number VARCHAR(24) NOT NULL, adults_number SMALLINT DEFAULT NULL, chuldren_number SMALLINT DEFAULT NULL, babies_number SMALLINT DEFAULT NULL, INDEX IDX_D84D7EBD56113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_joan ADD CONSTRAINT FK_D84D7EBD56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_joan DROP FOREIGN KEY FK_D84D7EBD56113E9E');
        $this->addSql('DROP TABLE transfer_joan');
    }
}
