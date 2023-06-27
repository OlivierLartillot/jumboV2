<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627083206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival ADD date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', ADD hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_departure ADD date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', ADD hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_inter_hotel ADD date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', ADD hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival DROP date, DROP hour');
        $this->addSql('ALTER TABLE transfer_departure DROP date, DROP hour');
        $this->addSql('ALTER TABLE transfer_inter_hotel DROP date, DROP hour');
    }
}
