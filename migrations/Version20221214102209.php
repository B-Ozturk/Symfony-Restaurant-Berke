<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221214102209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_review ADD menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE menu_review ADD CONSTRAINT FK_AA04D91ECCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_AA04D91ECCD7E912 ON menu_review (menu_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_review DROP FOREIGN KEY FK_AA04D91ECCD7E912');
        $this->addSql('DROP INDEX IDX_AA04D91ECCD7E912 ON menu_review');
        $this->addSql('ALTER TABLE menu_review DROP menu_id');
    }
}
