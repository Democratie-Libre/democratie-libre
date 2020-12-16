<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201216162521 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ProposalVersion DROP FOREIGN KEY FK_A04157F559027487');
        $this->addSql('DROP INDEX IDX_A04157F559027487 ON ProposalVersion');
        $this->addSql('ALTER TABLE ProposalVersion ADD themeTitle VARCHAR(255) NOT NULL, DROP theme_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ProposalVersion ADD theme_id INT DEFAULT NULL, DROP themeTitle');
        $this->addSql('ALTER TABLE ProposalVersion ADD CONSTRAINT FK_A04157F559027487 FOREIGN KEY (theme_id) REFERENCES Theme (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A04157F559027487 ON ProposalVersion (theme_id)');
    }
}
