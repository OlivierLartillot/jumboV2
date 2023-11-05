<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231105125507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C056113E9E');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('ALTER TABLE transfer_departure ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, INDEX IDX_4034A3C056113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C056113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE transfer_departure DROP adults_number, DROP children_number, DROP babies_number');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP adults_number, DROP children_number, DROP babies_number');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP adults_number, DROP children_number, DROP babies_number');
    }
}
