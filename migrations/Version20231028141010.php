<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231028141010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA453B9E377');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA46BF700BD');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA4A4A5EA4B');
        $this->addSql('ALTER TABLE customer_card DROP FOREIGN KEY FK_42853CA4D4D57CD');
        $this->addSql('DROP INDEX IDX_42853CA46BF700BD ON customer_card');
        $this->addSql('DROP INDEX IDX_42853CA4A4A5EA4B ON customer_card');
        $this->addSql('DROP INDEX IDX_42853CA453B9E377 ON customer_card');
        $this->addSql('DROP INDEX IDX_42853CA4D4D57CD ON customer_card');
        $this->addSql('ALTER TABLE customer_card DROP status_id, DROP status_updated_by_id, DROP meeting_point_id, DROP staff_id, DROP adults_number, DROP children_number, DROP babies_number, DROP status_updated_at, DROP meeting_at');
        $this->addSql('ALTER TABLE transfer_arrival ADD meeting_point_id INT DEFAULT NULL, ADD staff_id INT DEFAULT NULL, ADD status_id INT DEFAULT NULL, ADD status_updated_by_id INT DEFAULT NULL, ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL, ADD meeting_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD status_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF53B9E377 FOREIGN KEY (meeting_point_id) REFERENCES meeting_point (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFFD4D57CD FOREIGN KEY (staff_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFF6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE transfer_arrival ADD CONSTRAINT FK_E4502EFFA4A5EA4B FOREIGN KEY (status_updated_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_E4502EFF53B9E377 ON transfer_arrival (meeting_point_id)');
        $this->addSql('CREATE INDEX IDX_E4502EFFD4D57CD ON transfer_arrival (staff_id)');
        $this->addSql('CREATE INDEX IDX_E4502EFF6BF700BD ON transfer_arrival (status_id)');
        $this->addSql('CREATE INDEX IDX_E4502EFFA4A5EA4B ON transfer_arrival (status_updated_by_id)');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival ADD CONSTRAINT FK_93060361B8B5A9E1 FOREIGN KEY (transfer_arrival_id) REFERENCES transfer_arrival (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_93060361B8B5A9E1 ON transfer_vehicle_arrival (transfer_arrival_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_card ADD status_id INT NOT NULL, ADD status_updated_by_id INT NOT NULL, ADD meeting_point_id INT DEFAULT NULL, ADD staff_id INT DEFAULT NULL, ADD adults_number SMALLINT DEFAULT NULL, ADD children_number SMALLINT DEFAULT NULL, ADD babies_number SMALLINT DEFAULT NULL, ADD status_updated_at DATETIME NOT NULL, ADD meeting_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA453B9E377 FOREIGN KEY (meeting_point_id) REFERENCES meeting_point (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA46BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA4A4A5EA4B FOREIGN KEY (status_updated_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE customer_card ADD CONSTRAINT FK_42853CA4D4D57CD FOREIGN KEY (staff_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_42853CA46BF700BD ON customer_card (status_id)');
        $this->addSql('CREATE INDEX IDX_42853CA4A4A5EA4B ON customer_card (status_updated_by_id)');
        $this->addSql('CREATE INDEX IDX_42853CA453B9E377 ON customer_card (meeting_point_id)');
        $this->addSql('CREATE INDEX IDX_42853CA4D4D57CD ON customer_card (staff_id)');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF53B9E377');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFFD4D57CD');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFF6BF700BD');
        $this->addSql('ALTER TABLE transfer_arrival DROP FOREIGN KEY FK_E4502EFFA4A5EA4B');
        $this->addSql('DROP INDEX IDX_E4502EFF53B9E377 ON transfer_arrival');
        $this->addSql('DROP INDEX IDX_E4502EFFD4D57CD ON transfer_arrival');
        $this->addSql('DROP INDEX IDX_E4502EFF6BF700BD ON transfer_arrival');
        $this->addSql('DROP INDEX IDX_E4502EFFA4A5EA4B ON transfer_arrival');
        $this->addSql('ALTER TABLE transfer_arrival DROP meeting_point_id, DROP staff_id, DROP status_id, DROP status_updated_by_id, DROP adults_number, DROP children_number, DROP babies_number, DROP meeting_at, DROP status_updated_at');
        $this->addSql('ALTER TABLE transfer_vehicle_arrival DROP FOREIGN KEY FK_93060361B8B5A9E1');
        $this->addSql('DROP INDEX UNIQ_93060361B8B5A9E1 ON transfer_vehicle_arrival');
    }
}
