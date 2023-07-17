<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230717124430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer_vehicle_arrival (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, is_collective TINYINT(1) NOT NULL, vehicle_number SMALLINT DEFAULT NULL, vehicle_type VARCHAR(10) DEFAULT NULL, pick_up TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', transport_company VARCHAR(100) DEFAULT NULL, voucher_number VARCHAR(16) DEFAULT NULL, area VARCHAR(20) DEFAULT NULL, UNIQUE INDEX UNIQ_9306036156113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_9306036156113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_9306036156113E9E');
        $this->addSql('DROP TABLE transfer_vehicle_arrival');
    }
}
