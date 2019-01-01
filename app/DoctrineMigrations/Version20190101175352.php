<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190101175352 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $adminUsername = $this->container->getParameter('admin.name');
        $adminEmail = $this->container->getParameter('admin.email');
        $adminPassword = $this->container->getParameter('admin.pass');

        $sql = <<<SQL
INSERT INTO User (username, password, email, registrationDate, roles, banned) VALUES("%username%", "%password%", "%email%", NOW(), '%roles%', 0);
SQL;

        $sql = str_replace(
            ['%username%', '%password%', '%email%', '%roles%'],
            [
                $adminUsername,
                password_hash($adminPassword, \PASSWORD_BCRYPT, ['cost' => 13]),
                $adminEmail,
                serialize(['ROLE_USER', 'ROLE_ADMIN'])
            ],
            $sql
        );

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $sql = sprintf('DELETE FROM User WHERE username = "%s" AND email = "%s";',
            $this->container->getParameter('admin.name'),
            $this->container->getParameter('admin.email')
        );
        $this->addSql($sql);
    }
}
