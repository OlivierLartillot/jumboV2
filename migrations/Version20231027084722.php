<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027084722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD transfer_departure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_departure ADD CONSTRAINT FK_427273AD39D962C5 FOREIGN KEY (transfer_departure_id) REFERENCES transfer_departure (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_427273AD39D962C5 ON transfer_vehicle_departure (transfer_departure_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP FOREIGN KEY FK_427273AD39D962C5');
        $this->addSql('DROP INDEX UNIQ_427273AD39D962C5 ON transfer_vehicle_departure');
        $this->addSql('ALTER TABLE transfer_vehicle_departure DROP transfer_departure_id');
    }
}
