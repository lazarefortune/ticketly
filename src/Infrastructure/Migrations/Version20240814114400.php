<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814114400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_email_event ON ticket');
        $this->addSql('ALTER TABLE ticket DROP email, DROP phone_number');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97A0ADA3ECD2759F ON ticket (ticket_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_97A0ADA3ECD2759F ON ticket');
        $this->addSql('ALTER TABLE ticket ADD email VARCHAR(255) NOT NULL, ADD phone_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_email_event ON ticket (email, reservation_id)');
    }
}
