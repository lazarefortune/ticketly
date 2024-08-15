<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814002501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D700047D2');
        $this->addSql('DROP INDEX UNIQ_6D28840D700047D2 ON payment');
        $this->addSql('ALTER TABLE payment CHANGE ticket_id reservation_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840DB83297E7 ON payment (reservation_id)');
        $this->addSql('ALTER TABLE reservation ADD email VARCHAR(255) NOT NULL, ADD phone_number VARCHAR(255) DEFAULT NULL, ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA371F7E88B');
        $this->addSql('DROP INDEX IDX_97A0ADA371F7E88B ON ticket');
        $this->addSql('DROP INDEX unique_email_event ON ticket');
        $this->addSql('ALTER TABLE ticket CHANGE event_id reservation_id INT NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3B83297E7 ON ticket (reservation_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_email_event ON ticket (email, reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB83297E7');
        $this->addSql('DROP INDEX UNIQ_6D28840DB83297E7 ON payment');
        $this->addSql('ALTER TABLE payment CHANGE reservation_id ticket_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D700047D2 ON payment (ticket_id)');
        $this->addSql('ALTER TABLE reservation DROP email, DROP phone_number, DROP name');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3B83297E7');
        $this->addSql('DROP INDEX IDX_97A0ADA3B83297E7 ON ticket');
        $this->addSql('DROP INDEX unique_email_event ON ticket');
        $this->addSql('ALTER TABLE ticket CHANGE reservation_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_97A0ADA371F7E88B ON ticket (event_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_email_event ON ticket (email, event_id)');
    }
}
