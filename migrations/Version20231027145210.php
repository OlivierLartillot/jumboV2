<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027145210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_9306036156113E9E');
        $this->addSql('DROP INDEX UNIQ_9306036156113E9E ON transfer_vehicle_arrival');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival CHANGE customer_card_id transfer_arrival_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_93060361B8B5A9E1 FOREIGN KEY (transfer_arrival_id) REFERENCES transfer_arrival (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_93060361B8B5A9E1 ON transfer_vehicle_arrival (transfer_arrival_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_93060361B8B5A9E1');
        $this->addSql('DROP INDEX UNIQ_93060361B8B5A9E1 ON transfer_vehicle_arrival');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival CHANGE transfer_arrival_id customer_card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_9306036156113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9306036156113E9E ON transfer_vehicle_arrival (customer_card_id)');
    }
}
