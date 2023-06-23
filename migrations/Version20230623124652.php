<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623124652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer_arrival (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_id INT NOT NULL, to_arrival_id INT NOT NULL, service_number SMALLINT NOT NULL, date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(10) DEFAULT NULL, is_collective TINYINT(1) NOT NULL, INDEX IDX_E4502EFF56113E9E (customer_card_id), INDEX IDX_E4502EFF24D21901 (from_start_id), INDEX IDX_E4502EFF454B7575 (to_arrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF24D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF56113E9E');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF24D21901');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF454B7575');
        $this->addSql('DROP TABLE transfer_arrival');
    }
}
