<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221214101957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_review_menu DROP FOREIGN KEY FK_F74D69C7276EF1AF');
        $this->addSql('ALTER TABLE menu_review_menu DROP FOREIGN KEY FK_F74D69C7CCD7E912');
        $this->addSql('DROP TABLE menu_review_menu');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu_review_menu (menu_review_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_F74D69C7276EF1AF (menu_review_id), INDEX IDX_F74D69C7CCD7E912 (menu_id), PRIMARY KEY(menu_review_id, menu_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE menu_review_menu ADD CONSTRAINT FK_F74D69C7276EF1AF FOREIGN KEY (menu_review_id) REFERENCES menu_review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_review_menu ADD CONSTRAINT FK_F74D69C7CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
    }
}
