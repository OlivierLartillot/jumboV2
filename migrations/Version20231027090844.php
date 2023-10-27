<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027090844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_departure ADD transfer_vehicle_departure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_departure ADD CONSTRAINT FK_E86FFF1ADF83164 FOREIGN KEY (transfer_vehicle_departure_id) REFERENCES transfer_vehicle_departure (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E86FFF1ADF83164 ON transfer_departure (transfer_vehicle_departure_id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD transfer_vehicle_departure_id INT DEFAULT NULL, ADD transfer_vehicle_inter_hotel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492DF83164 FOREIGN KEY (transfer_vehicle_departure_id) REFERENCES transfer_vehicle_departure (id)');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD CONSTRAINT FK_20265492D1720DE4 FOREIGN KEY (transfer_vehicle_inter_hotel_id) REFERENCES transfer_vehicle_inter_hotel (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20265492DF83164 ON transfer_inter_hotel (transfer_vehicle_departure_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20265492D1720DE4 ON transfer_inter_hotel (transfer_vehicle_inter_hotel_id)');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP FOREIGN KEY FK_427273AD39D962C5');
        $this->addSql('DROP INDEX UNIQ_427273AD39D962C5 ON transfer_vehicle_departure');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP transfer_departure_id');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP FOREIGN KEY FK_6FAC09FB940CB423');
        $this->addSql('DROP INDEX UNIQ_6FAC09FB940CB423 ON transfer_vehicle_inter_hotel');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel DROP transfer_inter_hotel_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_departure DROP FOREIGN KEY FK_E86FFF1ADF83164');
        $this->addSql('DROP INDEX UNIQ_E86FFF1ADF83164 ON transfer_departure');
        $this->addSql('ALTER TABLE transfer_departure DROP transfer_vehicle_departure_id');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492DF83164');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP FOREIGN KEY FK_20265492D1720DE4');
        $this->addSql('DROP INDEX UNIQ_20265492DF83164 ON transfer_inter_hotel');
        $this->addSql('DROP INDEX UNIQ_20265492D1720DE4 ON transfer_inter_hotel');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP transfer_vehicle_departure_id, DROP transfer_vehicle_inter_hotel_id');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD transfer_departure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD CONSTRAINT FK_427273AD39D962C5 FOREIGN KEY (transfer_departure_id) REFERENCES transfer_departure (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_427273AD39D962C5 ON transfer_vehicle_departure (transfer_departure_id)');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD transfer_inter_hotel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_inter_hotel ADD CONSTRAINT FK_6FAC09FB940CB423 FOREIGN KEY (transfer_inter_hotel_id) REFERENCES transfer_inter_hotel (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FAC09FB940CB423 ON transfer_vehicle_inter_hotel (transfer_inter_hotel_id)');
    }
}
