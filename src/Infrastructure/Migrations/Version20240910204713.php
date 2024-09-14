<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910204713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coupon_events (coupon_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_EED4EB0966C5951B (coupon_id), INDEX IDX_EED4EB0971F7E88B (event_id), PRIMARY KEY(coupon_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coupon_events ADD CONSTRAINT FK_EED4EB0966C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coupon_events ADD CONSTRAINT FK_EED4EB0971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coupon_events DROP FOREIGN KEY FK_EED4EB0966C5951B');
        $this->addSql('ALTER TABLE coupon_events DROP FOREIGN KEY FK_EED4EB0971F7E88B');
        $this->addSql('DROP TABLE coupon_events');
    }
}
