<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230712093110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival DROP date_hour, CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE hour hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_departure CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE hour hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_inter_hotel CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE hour hour TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer_arrival ADD date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE hour hour TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_departure CHANGE date date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE hour hour TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE transfer_inter_hotel CHANGE date date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE hour hour TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }
}
