<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230112140122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_date (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, spots INT NOT NULL, available TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD reservation_date_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955DF028DEE FOREIGN KEY (reservation_date_id) REFERENCES reservation_date (id)');
        $this->addSql('CREATE INDEX IDX_42C84955DF028DEE ON reservation (reservation_date_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955DF028DEE');
        $this->addSql('DROP TABLE reservation_date');
        $this->addSql('DROP INDEX IDX_42C84955DF028DEE ON reservation');
        $this->addSql('ALTER TABLE reservation DROP reservation_date_id');
    }
}
