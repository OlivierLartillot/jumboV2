<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230621153352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_card ADD agency_id INT DEFAULT NULL, DROP agency');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA4CDEADB2A FOREIGN KEY (agency_id) REFERENCES agency (id)');
        $this->addSql('CREATE INDEX IDX_42853CA4CDEADB2A ON customer_card (agency_id)');
        $this->addSql('ALTER TABLE transfer CHANGE nature_transfer nature_transfer SMALLINT NOT NULL COMMENT \' 1=Arrivée, 2=inter hotel, 3=Départ\', CHANGE is_collective is_collective TINYINT(1) NOT NULL COMMENT \' 0=transport privé, 1= transport collectif\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA4CDEADB2A');
        $this->addSql('DROP TABLE agency');
        $this->addSql('DROP INDEX IDX_42853CA4CDEADB2A ON customer_card');
        $this->addSql('ALTER TABLE customer_card ADD agency VARCHAR(255) NOT NULL, DROP agency_id');
        $this->addSql('ALTER TABLE transfer CHANGE nature_transfer nature_transfer TINYINT(1) NOT NULL COMMENT \' 1=Arrivée, 2=inter hotel, 3=Départ\', CHANGE is_collective is_collective TINYINT(1) NOT NULL COMMENT \' 0=transport privé, 1= transport collectif\'');
    }
}
