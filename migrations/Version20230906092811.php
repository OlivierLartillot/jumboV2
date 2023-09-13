<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230906092811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting_point ADD es VARCHAR(100) DEFAULT NULL, ADD fr VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_point CHANGE name en VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE meeting_point ADD it VARCHAR(100) DEFAULT NULL');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting_point DROP es, DROP fr');
        $this->addSql('ALTER TABLE meeting_point CHANGE en name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE meeting_point DROP it');
    }
}
