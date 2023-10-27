<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027083224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transport_company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_arrival DROP is_collective');
        $this->addSql('ALTER TABLE transfer_departure DROP service_number, DROP is_collective');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP service_number, DROP is_collective');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD transport_company_id INT DEFAULT NULL, DROP transport_company, DROP reservation_number');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_93060361F0D0939C FOREIGN KEY (transport_company_id) REFERENCES transport_company (id)');
        $this->addSql('CREATE INDEX IDX_93060361F0D0939C ON transfer_vehicle_arrival (transport_company_id)');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD transport_company_id INT DEFAULT NULL, DROP date, DROP transport_company, DROP reservation_number');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD CONSTRAINT FK_427273ADF0D0939C FOREIGN KEY (transport_company_id) REFERENCES transport_company (id)');
        $this->addSql('CREATE INDEX IDX_427273ADF0D0939C ON transfer_vehicle_departure (transport_company_id)');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD transport_company_id INT DEFAULT NULL, DROP date, DROP transport_company, DROP reservation_number');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD CONSTRAINT FK_6FAC09FBF0D0939C FOREIGN KEY (transport_company_id) REFERENCES transport_company (id)');
        $this->addSql('CREATE INDEX IDX_6FAC09FBF0D0939C ON transfer_vehicle_inter_hotel (transport_company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_93060361F0D0939C');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP FOREIGN KEY FK_427273ADF0D0939C');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP FOREIGN KEY FK_6FAC09FBF0D0939C');
        $this->addSql('DROP TABLE transport_company');
        $this->addSql('ALTER TABLE transfer_arrival ADD is_collective TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE transfer_departure ADD service_number VARCHAR(50) NOT NULL, ADD is_collective TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD service_number VARCHAR(50) NOT NULL, ADD is_collective TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX IDX_93060361F0D0939C ON transfer_vehicle_arrival');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD transport_company VARCHAR(100) DEFAULT NULL, ADD reservation_number VARCHAR(30) DEFAULT NULL, DROP transport_company_id');
        $this->addSql('DROP INDEX IDX_427273ADF0D0939C ON transfer_vehicle_departure');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD transport_company VARCHAR(100) DEFAULT NULL, ADD reservation_number VARCHAR(30) DEFAULT NULL, DROP transport_company_id');
        $this->addSql('DROP INDEX IDX_6FAC09FBF0D0939C ON transfer_vehicle_inter_hotel');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD transport_company VARCHAR(100) DEFAULT NULL, ADD reservation_number VARCHAR(30) DEFAULT NULL, DROP transport_company_id');
    }
}
