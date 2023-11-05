<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231105095640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer_inter_hotel (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, transport_company_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', pick_up VARCHAR(6) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, vehicle_number SMALLINT DEFAULT NULL, vehicle_type VARCHAR(10) DEFAULT NULL, voucher_number VARCHAR(16) DEFAULT NULL, area VARCHAR(20) DEFAULT NULL, INDEX IDX_2026549256113E9E (customer_card_id), INDEX IDX_2026549224D21901 (from_start_id), INDEX IDX_20265492454B7575 (to_arrival_id), INDEX IDX_20265492F0D0939C (transport_company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_2026549256113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_2026549224D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492F0D0939C FOREIGN KEY (transport_company_id) REFERENCES transport_company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_2026549256113E9E');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_2026549224D21901');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492454B7575');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492F0D0939C');
        $this->addSql('DROP TABLE transfer_inter_hotel');
    }
}
