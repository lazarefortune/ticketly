<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911144352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_collaborator (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, collaborator_id INT DEFAULT NULL, roles JSON NOT NULL, INDEX IDX_8D7E428F71F7E88B (event_id), INDEX IDX_8D7E428F30098C8C (collaborator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_collaborator ADD CONSTRAINT FK_8D7E428F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_collaborator ADD CONSTRAINT FK_8D7E428F30098C8C FOREIGN KEY (collaborator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE coupon_events DROP FOREIGN KEY FK_EED4EB0966C5951B');
        $this->addSql('ALTER TABLE coupon_events DROP FOREIGN KEY FK_EED4EB0971F7E88B');
        $this->addSql('DROP TABLE coupon_events');
        $this->addSql('ALTER TABLE coupon ADD event_id INT NOT NULL');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F0271F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_64BF3F0271F7E88B ON coupon (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coupon_events (coupon_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_EED4EB0966C5951B (coupon_id), INDEX IDX_EED4EB0971F7E88B (event_id), PRIMARY KEY(coupon_id, event_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE coupon_events ADD CONSTRAINT FK_EED4EB0966C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coupon_events ADD CONSTRAINT FK_EED4EB0971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_collaborator DROP FOREIGN KEY FK_8D7E428F71F7E88B');
        $this->addSql('ALTER TABLE event_collaborator DROP FOREIGN KEY FK_8D7E428F30098C8C');
        $this->addSql('DROP TABLE event_collaborator');
        $this->addSql('ALTER TABLE coupon DROP FOREIGN KEY FK_64BF3F0271F7E88B');
        $this->addSql('DROP INDEX IDX_64BF3F0271F7E88B ON coupon');
        $this->addSql('ALTER TABLE coupon DROP event_id');
    }
}
