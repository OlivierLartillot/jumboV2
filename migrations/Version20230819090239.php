<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230819090239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE printing_options (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_713F3BFDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE printing_options_airport_hotel (printing_options_id INT NOT NULL, airport_hotel_id INT NOT NULL, INDEX IDX_BCDB58345189E7C7 (printing_options_id), INDEX IDX_BCDB583478BD9DC (airport_hotel_id), PRIMARY KEY(printing_options_id, airport_hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE printing_options ADD CONSTRAINT FK_713F3BFDA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE printing_options_airport_hotel ADD CONSTRAINT FK_BCDB58345189E7C7 FOREIGN KEY (printing_options_id) REFERENCES printing_options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE printing_options_airport_hotel ADD CONSTRAINT FK_BCDB583478BD9DC FOREIGN KEY (airport_hotel_id) REFERENCES airport_hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE authorized_to_print DROP FOREIGN KEY FK_631A178D71179CD6');
        $this->addSql('ALTER TABLE authorized_to_print_airport_hotel DROP FOREIGN KEY FK_978C89D78BD9DC');
        $this->addSql('ALTER TABLE authorized_to_print_airport_hotel DROP FOREIGN KEY FK_978C89D774D306C');
        $this->addSql('DROP TABLE authorized_to_print');
        $this->addSql('DROP TABLE authorized_to_print_airport_hotel');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authorized_to_print (id INT AUTO_INCREMENT NOT NULL, name_id INT NOT NULL, UNIQUE INDEX UNIQ_631A178D71179CD6 (name_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE authorized_to_print_airport_hotel (authorized_to_print_id INT NOT NULL, airport_hotel_id INT NOT NULL, INDEX IDX_978C89D774D306C (authorized_to_print_id), INDEX IDX_978C89D78BD9DC (airport_hotel_id), PRIMARY KEY(authorized_to_print_id, airport_hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE authorized_to_print ADD CONSTRAINT FK_631A178D71179CD6 FOREIGN KEY (name_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE authorized_to_print_airport_hotel ADD CONSTRAINT FK_978C89D78BD9DC FOREIGN KEY (airport_hotel_id) REFERENCES airport_hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE authorized_to_print_airport_hotel ADD CONSTRAINT FK_978C89D774D306C FOREIGN KEY (authorized_to_print_id) REFERENCES authorized_to_print (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE printing_options DROP FOREIGN KEY FK_713F3BFDA76ED395');
        $this->addSql('ALTER TABLE printing_options_airport_hotel DROP FOREIGN KEY FK_BCDB58345189E7C7');
        $this->addSql('ALTER TABLE printing_options_airport_hotel DROP FOREIGN KEY FK_BCDB583478BD9DC');
        $this->addSql('DROP TABLE printing_options');
        $this->addSql('DROP TABLE printing_options_airport_hotel');
    }
}
