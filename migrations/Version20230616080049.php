<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616080049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE area (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, customer_card_id INT NOT NULL, predefined_comments_messages_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, media VARCHAR(255) DEFAULT NULL, INDEX IDX_9474526CB03A8386 (created_by_id), INDEX IDX_9474526C56113E9E (customer_card_id), INDEX IDX_9474526CDEA22706 (predefined_comments_messages_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_card (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, status_updated_by_id INT NOT NULL, meeting_point_id INT DEFAULT NULL, staff_id INT DEFAULT NULL, reservation_number VARCHAR(24) NOT NULL, jumbo_number VARCHAR(24) NOT NULL, holder VARCHAR(255) NOT NULL, agency VARCHAR(255) NOT NULL, adults_number SMALLINT DEFAULT NULL, children_number SMALLINT DEFAULT NULL, babies_number SMALLINT DEFAULT NULL, status_updated_at DATETIME NOT NULL, meeting_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', reservation_cancelled TINYINT(1) DEFAULT NULL, INDEX IDX_42853CA46BF700BD (status_id), INDEX IDX_42853CA4A4A5EA4B (status_updated_by_id), INDEX IDX_42853CA453B9E377 (meeting_point_id), INDEX IDX_42853CA4D4D57CD (staff_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_report (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT DEFAULT NULL, title VARCHAR(100) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EAABD556113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE drag_and_drop (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_point (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE predefined_comments_messages (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, message_to_display VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status_history (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, customer_card_id INT NOT NULL, updated_by_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2F6A07CE6BF700BD (status_id), INDEX IDX_2F6A07CE56113E9E (customer_card_id), INDEX IDX_2F6A07CE896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, service_number VARCHAR(24) DEFAULT NULL, nature_transfer VARCHAR(24) NOT NULL, date_hour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number VARCHAR(24) DEFAULT NULL, from_start VARCHAR(255) NOT NULL, to_arrival VARCHAR(255) NOT NULL, private_collective VARCHAR(24) DEFAULT NULL, adults_number SMALLINT DEFAULT NULL, children_number SMALLINT DEFAULT NULL, babies_number SMALLINT DEFAULT NULL, INDEX IDX_4034A3C056113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_joan (id INT AUTO_INCREMENT NOT NULL, customer_card_id INT NOT NULL, from_start_joan VARCHAR(255) NOT NULL, to_arrival_joan VARCHAR(255) NOT NULL, date_hour_joan DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flight_number_joan VARCHAR(24) DEFAULT NULL, private_collective_joan VARCHAR(24) DEFAULT NULL, pickup_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', transport_company VARCHAR(60) NOT NULL, vehicle_number SMALLINT NOT NULL, vehicle_type VARCHAR(24) NOT NULL, transfer_area VARCHAR(24) NOT NULL, voucher_number VARCHAR(24) NOT NULL, adults_number SMALLINT DEFAULT NULL, chuldren_number SMALLINT DEFAULT NULL, babies_number SMALLINT DEFAULT NULL, nature_transfer VARCHAR(50) NOT NULL, INDEX IDX_D84D7EBD56113E9E (customer_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, area_id INT NOT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, last_connection DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), INDEX IDX_8D93D649BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CDEA22706 FOREIGN KEY (predefined_comments_messages_id) REFERENCES predefined_comments_messages (id)');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA46BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA4A4A5EA4B FOREIGN KEY (status_updated_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA453B9E377 FOREIGN KEY (meeting_point_id) REFERENCES meeting_point (id)');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA4D4D57CD FOREIGN KEY (staff_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer_report ADD CONSTRAINT FK_75EAABD556113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE status_history ADD CONSTRAINT FK_2F6A07CE6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE status_history ADD CONSTRAINT FK_2F6A07CE56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE status_history ADD CONSTRAINT FK_2F6A07CE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C056113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE transfer_joan ADD CONSTRAINT FK_D84D7EBD56113E9E FOREIGN KEY (customer_card_id) REFERENCES customer_card (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CB03A8386');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C56113E9E');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CDEA22706');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA46BF700BD');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA4A4A5EA4B');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA453B9E377');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA4D4D57CD');
        $this->addSql('ALTER TABLE customer_report DROP FOREIGN KEY FK_75EAABD556113E9E');
        $this->addSql('ALTER TABLE status_history DROP FOREIGN KEY FK_2F6A07CE6BF700BD');
        $this->addSql('ALTER TABLE status_history DROP FOREIGN KEY FK_2F6A07CE56113E9E');
        $this->addSql('ALTER TABLE status_history DROP FOREIGN KEY FK_2F6A07CE896DBBDE');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C056113E9E');
        $this->addSql('ALTER TABLE transfer_joan DROP FOREIGN KEY FK_D84D7EBD56113E9E');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649BD0F409C');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE customer_card');
        $this->addSql('DROP TABLE customer_report');
        $this->addSql('DROP TABLE drag_and_drop');
        $this->addSql('DROP TABLE meeting_point');
        $this->addSql('DROP TABLE predefined_comments_messages');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE status_history');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('DROP TABLE transfer_joan');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
