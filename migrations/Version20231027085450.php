<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027085450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP FOREIGN KEY FK_427273AD56113E9E');
        $this->addSql('DROP INDEX UNIQ_427273AD56113E9E ON transfer_vehicle_departure');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP customer_card_id');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP FOREIGN KEY FK_6FAC09FB56113E9E');
        $this->addSql('DROP INDEX IDX_6FAC09FB56113E9E ON transfer_vehicle_inter_hotel');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel CHANGE customer_card_id transfer_inter_hotel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD CONSTRAINT FK_6FAC09FB940CB423 FOREIGN KEY (transfer_inter_hotel_id) REFERENCES transfer_inter_hotel (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FAC09FB940CB423 ON transfer_vehicle_inter_hotel (transfer_inter_hotel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD customer_card_id INT NOT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD CONSTRAINT FK_427273AD56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_427273AD56113E9E ON transfer_vehicle_departure (customer_card_id)');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP FOREIGN KEY FK_6FAC09FB940CB423');
        $this->addSql('DROP INDEX UNIQ_6FAC09FB940CB423 ON transfer_vehicle_inter_hotel');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel CHANGE transfer_inter_hotel_id customer_card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD CONSTRAINT FK_6FAC09FB56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6FAC09FB56113E9E ON transfer_vehicle_inter_hotel (customer_card_id)');
    }
}
