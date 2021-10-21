<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210824211910 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // up() migration written manually
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE Proposal SET status = \'published\' where status = \'\'');
    }

    public function down(Schema $schema) : void
    {
        // down() migration written manually
        throw new Exception('This migration is not reversible.');
    }
}
