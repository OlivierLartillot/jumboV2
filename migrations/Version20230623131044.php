<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623131044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C024D21901');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0454B7575');
        $this->addSql('DROP INDEX IDX_4034A3C024D21901 ON transfer');
        $this->addSql('DROP INDEX IDX_4034A3C0454B7575 ON transfer');
        $this->addSql('ALTER TABLE transfer DROP from_start_id, DROP to_arrival_id, DROP service_number, DROP nature_transfer, DROP date_hour, DROP flight_number, DROP adults_number, DROP children_number, DROP babies_number, DROP is_collective');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer ADD from_start_id INT NOT NULL, ADD to_arrival_id INT NOT NULL, ADD service_number VARCHAR(24) DEFAULT NULL, ADD nature_transfer SMALLINT NOT NULL, ADD date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD flight_number VARCHAR(24) DEFAULT NULL, ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL, ADD is_collective TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C024D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('CREATE INDEX IDX_4034A3C024D21901 ON transfer (from_start_id)');
        $this->addSql('CREATE INDEX IDX_4034A3C0454B7575 ON transfer (to_arrival_id)');
    }
}
