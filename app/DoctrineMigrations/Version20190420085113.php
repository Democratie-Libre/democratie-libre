<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190420085113 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Theme (id INT AUTO_INCREMENT NOT NULL, theme_parent_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, title VARCHAR(255) NOT NULL, abstract LONGTEXT NOT NULL, creationDate DATETIME NOT NULL, editDate DATETIME NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, root INT DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, temp VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_56B4C80C989D9B62 (slug), UNIQUE INDEX UNIQ_56B4C80C2B36786B (title), INDEX IDX_56B4C80CBCB910FD (theme_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AbstractDiscussion (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, proposal_id INT DEFAULT NULL, article_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, title VARCHAR(255) NOT NULL, creationDate DATETIME NOT NULL, lastEditDate DATETIME NOT NULL, locked TINYINT(1) NOT NULL, discr VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_4AAE0F32989D9B62 (slug), UNIQUE INDEX UNIQ_4AAE0F322B36786B (title), INDEX IDX_4AAE0F3259027487 (theme_id), INDEX IDX_4AAE0F32F4792058 (proposal_id), INDEX IDX_4AAE0F327294869C (article_id), INDEX IDX_4AAE0F32642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discussions_unreaders (abstractdiscussion_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_730C5DD7D3EE0889 (abstractdiscussion_id), INDEX IDX_730C5DD7A76ED395 (user_id), PRIMARY KEY(abstractdiscussion_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE public_discussions_followers (publicdiscussion_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_CEBAE29B67B4699D (publicdiscussion_id), INDEX IDX_CEBAE29BA76ED395 (user_id), PRIMARY KEY(publicdiscussion_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE private_discussions_members (privatediscussion_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6D81258816F819BA (privatediscussion_id), INDEX IDX_6D812588A76ED395 (user_id), PRIMARY KEY(privatediscussion_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Proposal (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, author_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, title VARCHAR(255) NOT NULL, abstract LONGTEXT NOT NULL, creationDate DATETIME NOT NULL, lastEditDate DATETIME NOT NULL, motivation LONGTEXT DEFAULT NULL, versionNumber INT NOT NULL, isAWiki TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_4693F624989D9B62 (slug), UNIQUE INDEX UNIQ_4693F6242B36786B (title), INDEX IDX_4693F62459027487 (theme_id), INDEX IDX_4693F624F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposals_supporters (proposal_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C7681A25F4792058 (proposal_id), INDEX IDX_C7681A25A76ED395 (user_id), PRIMARY KEY(proposal_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposals_opposents (proposal_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1DFDCEE4F4792058 (proposal_id), INDEX IDX_1DFDCEE4A76ED395 (user_id), PRIMARY KEY(proposal_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Post (id INT AUTO_INCREMENT NOT NULL, discussion_id INT DEFAULT NULL, author_id INT DEFAULT NULL, date DATETIME NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_FAB8C3B31ADED311 (discussion_id), INDEX IDX_FAB8C3B3F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ArticleVersion (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(128) NOT NULL, number INT NOT NULL, title VARCHAR(255) NOT NULL, snapDate DATETIME NOT NULL, content LONGTEXT DEFAULT NULL, motivation LONGTEXT DEFAULT NULL, versionNumber INT NOT NULL, recordedArticle_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_2E31E284989D9B62 (slug), INDEX IDX_2E31E2846127E0B7 (recordedArticle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Article (id INT AUTO_INCREMENT NOT NULL, proposal_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, number INT NOT NULL, title VARCHAR(255) NOT NULL, creationDate DATETIME NOT NULL, lastEditDate DATETIME NOT NULL, content LONGTEXT DEFAULT NULL, motivation LONGTEXT DEFAULT NULL, versionNumber INT NOT NULL, UNIQUE INDEX UNIQ_CD8737FA989D9B62 (slug), UNIQUE INDEX UNIQ_CD8737FA2B36786B (title), INDEX IDX_CD8737FAF4792058 (proposal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(30) NOT NULL, password VARCHAR(64) NOT NULL, email VARCHAR(60) NOT NULL, registrationDate DATETIME NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', banned TINYINT(1) NOT NULL, path VARCHAR(255) DEFAULT NULL, temp VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_2DA17977F85E0677 (username), UNIQUE INDEX UNIQ_2DA17977E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ProposalVersion (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, author_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, title VARCHAR(255) NOT NULL, abstract LONGTEXT NOT NULL, snapDate DATETIME NOT NULL, motivation LONGTEXT DEFAULT NULL, versionNumber INT NOT NULL, isAWiki TINYINT(1) NOT NULL, recordedProposal_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A04157F5989D9B62 (slug), INDEX IDX_A04157F558D66A7E (recordedProposal_id), INDEX IDX_A04157F559027487 (theme_id), INDEX IDX_A04157F5F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposalversion_articleversion (proposalversion_id INT NOT NULL, articleversion_id INT NOT NULL, INDEX IDX_B8A11D7F337475DE (proposalversion_id), INDEX IDX_B8A11D7FDC15D63C (articleversion_id), PRIMARY KEY(proposalversion_id, articleversion_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposalVersions_supporters (proposalversion_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_845FA79A337475DE (proposalversion_id), INDEX IDX_845FA79AA76ED395 (user_id), PRIMARY KEY(proposalversion_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposalVersions_opponents (proposalversion_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DBD948BF337475DE (proposalversion_id), INDEX IDX_DBD948BFA76ED395 (user_id), PRIMARY KEY(proposalversion_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Theme ADD CONSTRAINT FK_56B4C80CBCB910FD FOREIGN KEY (theme_parent_id) REFERENCES Theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE AbstractDiscussion ADD CONSTRAINT FK_4AAE0F3259027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('ALTER TABLE AbstractDiscussion ADD CONSTRAINT FK_4AAE0F32F4792058 FOREIGN KEY (proposal_id) REFERENCES Proposal (id)');
        $this->addSql('ALTER TABLE AbstractDiscussion ADD CONSTRAINT FK_4AAE0F327294869C FOREIGN KEY (article_id) REFERENCES Article (id)');
        $this->addSql('ALTER TABLE AbstractDiscussion ADD CONSTRAINT FK_4AAE0F32642B8210 FOREIGN KEY (admin_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE discussions_unreaders ADD CONSTRAINT FK_730C5DD7D3EE0889 FOREIGN KEY (abstractdiscussion_id) REFERENCES AbstractDiscussion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discussions_unreaders ADD CONSTRAINT FK_730C5DD7A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE public_discussions_followers ADD CONSTRAINT FK_CEBAE29B67B4699D FOREIGN KEY (publicdiscussion_id) REFERENCES AbstractDiscussion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE public_discussions_followers ADD CONSTRAINT FK_CEBAE29BA76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE private_discussions_members ADD CONSTRAINT FK_6D81258816F819BA FOREIGN KEY (privatediscussion_id) REFERENCES AbstractDiscussion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE private_discussions_members ADD CONSTRAINT FK_6D812588A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Proposal ADD CONSTRAINT FK_4693F62459027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('ALTER TABLE Proposal ADD CONSTRAINT FK_4693F624F675F31B FOREIGN KEY (author_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE proposals_supporters ADD CONSTRAINT FK_C7681A25F4792058 FOREIGN KEY (proposal_id) REFERENCES Proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposals_supporters ADD CONSTRAINT FK_C7681A25A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposals_opposents ADD CONSTRAINT FK_1DFDCEE4F4792058 FOREIGN KEY (proposal_id) REFERENCES Proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposals_opposents ADD CONSTRAINT FK_1DFDCEE4A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Post ADD CONSTRAINT FK_FAB8C3B31ADED311 FOREIGN KEY (discussion_id) REFERENCES AbstractDiscussion (id)');
        $this->addSql('ALTER TABLE Post ADD CONSTRAINT FK_FAB8C3B3F675F31B FOREIGN KEY (author_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE ArticleVersion ADD CONSTRAINT FK_2E31E2846127E0B7 FOREIGN KEY (recordedArticle_id) REFERENCES Article (id)');
        $this->addSql('ALTER TABLE Article ADD CONSTRAINT FK_CD8737FAF4792058 FOREIGN KEY (proposal_id) REFERENCES Proposal (id)');
        $this->addSql('ALTER TABLE ProposalVersion ADD CONSTRAINT FK_A04157F558D66A7E FOREIGN KEY (recordedProposal_id) REFERENCES Proposal (id)');
        $this->addSql('ALTER TABLE ProposalVersion ADD CONSTRAINT FK_A04157F559027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('ALTER TABLE ProposalVersion ADD CONSTRAINT FK_A04157F5F675F31B FOREIGN KEY (author_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE proposalversion_articleversion ADD CONSTRAINT FK_B8A11D7F337475DE FOREIGN KEY (proposalversion_id) REFERENCES ProposalVersion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposalversion_articleversion ADD CONSTRAINT FK_B8A11D7FDC15D63C FOREIGN KEY (articleversion_id) REFERENCES ArticleVersion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposalVersions_supporters ADD CONSTRAINT FK_845FA79A337475DE FOREIGN KEY (proposalversion_id) REFERENCES ProposalVersion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposalVersions_supporters ADD CONSTRAINT FK_845FA79AA76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposalVersions_opponents ADD CONSTRAINT FK_DBD948BF337475DE FOREIGN KEY (proposalversion_id) REFERENCES ProposalVersion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposalVersions_opponents ADD CONSTRAINT FK_DBD948BFA76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Theme DROP FOREIGN KEY FK_56B4C80CBCB910FD');
        $this->addSql('ALTER TABLE AbstractDiscussion DROP FOREIGN KEY FK_4AAE0F3259027487');
        $this->addSql('ALTER TABLE Proposal DROP FOREIGN KEY FK_4693F62459027487');
        $this->addSql('ALTER TABLE ProposalVersion DROP FOREIGN KEY FK_A04157F559027487');
        $this->addSql('ALTER TABLE discussions_unreaders DROP FOREIGN KEY FK_730C5DD7D3EE0889');
        $this->addSql('ALTER TABLE public_discussions_followers DROP FOREIGN KEY FK_CEBAE29B67B4699D');
        $this->addSql('ALTER TABLE private_discussions_members DROP FOREIGN KEY FK_6D81258816F819BA');
        $this->addSql('ALTER TABLE Post DROP FOREIGN KEY FK_FAB8C3B31ADED311');
        $this->addSql('ALTER TABLE AbstractDiscussion DROP FOREIGN KEY FK_4AAE0F32F4792058');
        $this->addSql('ALTER TABLE proposals_supporters DROP FOREIGN KEY FK_C7681A25F4792058');
        $this->addSql('ALTER TABLE proposals_opposents DROP FOREIGN KEY FK_1DFDCEE4F4792058');
        $this->addSql('ALTER TABLE Article DROP FOREIGN KEY FK_CD8737FAF4792058');
        $this->addSql('ALTER TABLE ProposalVersion DROP FOREIGN KEY FK_A04157F558D66A7E');
        $this->addSql('ALTER TABLE proposalversion_articleversion DROP FOREIGN KEY FK_B8A11D7FDC15D63C');
        $this->addSql('ALTER TABLE AbstractDiscussion DROP FOREIGN KEY FK_4AAE0F327294869C');
        $this->addSql('ALTER TABLE ArticleVersion DROP FOREIGN KEY FK_2E31E2846127E0B7');
        $this->addSql('ALTER TABLE AbstractDiscussion DROP FOREIGN KEY FK_4AAE0F32642B8210');
        $this->addSql('ALTER TABLE discussions_unreaders DROP FOREIGN KEY FK_730C5DD7A76ED395');
        $this->addSql('ALTER TABLE public_discussions_followers DROP FOREIGN KEY FK_CEBAE29BA76ED395');
        $this->addSql('ALTER TABLE private_discussions_members DROP FOREIGN KEY FK_6D812588A76ED395');
        $this->addSql('ALTER TABLE Proposal DROP FOREIGN KEY FK_4693F624F675F31B');
        $this->addSql('ALTER TABLE proposals_supporters DROP FOREIGN KEY FK_C7681A25A76ED395');
        $this->addSql('ALTER TABLE proposals_opposents DROP FOREIGN KEY FK_1DFDCEE4A76ED395');
        $this->addSql('ALTER TABLE Post DROP FOREIGN KEY FK_FAB8C3B3F675F31B');
        $this->addSql('ALTER TABLE ProposalVersion DROP FOREIGN KEY FK_A04157F5F675F31B');
        $this->addSql('ALTER TABLE proposalVersions_supporters DROP FOREIGN KEY FK_845FA79AA76ED395');
        $this->addSql('ALTER TABLE proposalVersions_opponents DROP FOREIGN KEY FK_DBD948BFA76ED395');
        $this->addSql('ALTER TABLE proposalversion_articleversion DROP FOREIGN KEY FK_B8A11D7F337475DE');
        $this->addSql('ALTER TABLE proposalVersions_supporters DROP FOREIGN KEY FK_845FA79A337475DE');
        $this->addSql('ALTER TABLE proposalVersions_opponents DROP FOREIGN KEY FK_DBD948BF337475DE');
        $this->addSql('DROP TABLE Theme');
        $this->addSql('DROP TABLE AbstractDiscussion');
        $this->addSql('DROP TABLE discussions_unreaders');
        $this->addSql('DROP TABLE public_discussions_followers');
        $this->addSql('DROP TABLE private_discussions_members');
        $this->addSql('DROP TABLE Proposal');
        $this->addSql('DROP TABLE proposals_supporters');
        $this->addSql('DROP TABLE proposals_opposents');
        $this->addSql('DROP TABLE Post');
        $this->addSql('DROP TABLE ArticleVersion');
        $this->addSql('DROP TABLE Article');
        $this->addSql('DROP TABLE User');
        $this->addSql('DROP TABLE ProposalVersion');
        $this->addSql('DROP TABLE proposalversion_articleversion');
        $this->addSql('DROP TABLE proposalVersions_supporters');
        $this->addSql('DROP TABLE proposalVersions_opponents');
    }
}
