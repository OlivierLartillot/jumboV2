<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027143312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492DF83164');
        $this->addSql('DROP INDEX UNIQ_20265492DF83164 ON transfer_inter_hotel');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP transfer_vehicle_departure_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD transfer_vehicle_departure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492DF83164 FOREIGN KEY (transfer_vehicle_departure_id) REFERENCES transfer_vehicle_departure (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20265492DF83164 ON transfer_inter_hotel (transfer_vehicle_departure_id)');
    }
}
