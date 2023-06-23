<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230621151757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer ADD is_collective TINYINT(1) NOT NULL, DROP private_collective, CHANGE nature_transfer nature_transfer SMALLINT NOT NULL COMMENT " 0=transport privé, 1= transport collectif";');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer ADD private_collective VARCHAR(24) DEFAULT NULL, DROP is_collective, CHANGE nature_transfer nature_transfer SMALLINT NOT NULL COMMENT \'1=arrivée, 2=inter hotel,
        3=départ\'');
    }
}
