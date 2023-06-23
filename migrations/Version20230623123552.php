<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623123552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer_departure (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, service_number SMALLINT NOT NULL, date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(10) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, INDEX IDX_E86FFF1A56113E9E (customer_card_id), INDEX IDX_E86FFF1A24D21901 (from_start_id), INDEX IDX_E86FFF1A454B7575 (to_arrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_inter_hotel (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, service_number SMALLINT NOT NULL, date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(10) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, INDEX IDX_2026549256113E9E (customer_card_id), INDEX IDX_2026549224D21901 (from_start_id), INDEX IDX_20265492454B7575 (to_arrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1A56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1A24D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1A454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_2026549256113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_2026549224D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('DROP TABLE drag_and_drop');
        $this->addSql('ALTER TABLE transfer CHANGE nature_transfer nature_transfer SMALLINT NOT NULL, CHANGE is_collective is_collective TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE drag_and_drop (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1A56113E9E');
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1A24D21901');
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1A454B7575');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_2026549256113E9E');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_2026549224D21901');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492454B7575');
        $this->addSql('DROP TABLE transfer_departure');
        $this->addSql('DROP TABLE transfer_inter_hotel');
        $this->addSql('ALTER TABLE transfer CHANGE nature_transfer nature_transfer SMALLINT NOT NULL COMMENT \' 1=Arrivée, 2=inter hotel, 3=Départ\', CHANGE is_collective is_collective TINYINT(1) NOT NULL COMMENT \' 0=transport privé, 1= transport collectif\'');
    }
}
