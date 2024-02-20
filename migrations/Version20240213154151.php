<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240213154151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD to_arrival_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_93060361454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('CREATE INDEX IDX_93060361454B7575 ON transfer_vehicle_arrival (to_arrival_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_93060361454B7575');
        $this->addSql('DROP INDEX IDX_93060361454B7575 ON transfer_vehicle_arrival');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP to_arrival_id');
    }
}
