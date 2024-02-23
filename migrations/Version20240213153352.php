<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240213153352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD from_start_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_9306036124D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('CREATE INDEX IDX_9306036124D21901 ON transfer_vehicle_arrival (from_start_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_9306036124D21901');
        $this->addSql('DROP INDEX IDX_9306036124D21901 ON transfer_vehicle_arrival');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP from_start_id');
    }
}
