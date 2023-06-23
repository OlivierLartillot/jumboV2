<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230621140030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       $this->addSql('ALTER TABLE transfer ADD from_start_id INT NOT NULL, ADD to_arrival_id INT NOT NULL, DROP from_start, DROP to_arrival');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C024D21901 FOREIGN KEY (from_start_id) REFERENCES airport_hotel (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0454B7575 FOREIGN KEY (to_arrival_id) REFERENCES airport_hotel (id)');
        $this->addSql('CREATE INDEX IDX_4034A3C024D21901 ON transfer (from_start_id)');
        $this->addSql('CREATE INDEX IDX_4034A3C0454B7575 ON transfer (to_arrival_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C024D21901');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0454B7575');
        $this->addSql('DROP TABLE airport_hotel');
        $this->addSql('DROP TABLE drag_and_drop');
        $this->addSql('DROP INDEX IDX_4034A3C024D21901 ON transfer');
        $this->addSql('DROP INDEX IDX_4034A3C0454B7575 ON transfer');
        $this->addSql('ALTER TABLE transfer ADD from_start VARCHAR(255) NOT NULL, ADD to_arrival VARCHAR(255) NOT NULL, DROP from_start_id, DROP to_arrival_id');
    }
}
