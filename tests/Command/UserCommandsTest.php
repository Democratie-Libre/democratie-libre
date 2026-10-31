<?php

namespace Tests\Command;

use App\Entity\Theme;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\AppTestCase;

class UserCommandsTest extends AppTestCase
{
    private function runCommand($name, array $arguments)
    {
        $application = new Application(static::$kernel);
        $tester      = new CommandTester($application->find($name));
        $tester->execute($arguments);

        return $tester;
    }

    public function testCreateUser()
    {
        $this->runCommand('app:create-user', ['username' => 'bob', 'email' => 'bob@example.com', 'password' => 'bobpass']);

        $user = $this->em->getRepository(User::class)->findOneByUsername('bob');
        $this->assertNotNull($user);
        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
        $this->assertNotSame('bobpass', $user->getPassword());
    }

    public function testCreateAdminUser()
    {
        $this->runCommand('app:create-user', ['username' => 'root', 'email' => 'root@example.com', 'password' => 'rootpass', '--admin' => true]);

        $this->assertContains('ROLE_ADMIN', $this->em->getRepository(User::class)->findOneByUsername('root')->getRoles());
    }

    public function testChangeUserPassword()
    {
        $oldHash = $this->data['user']->getPassword();
        $this->runCommand('app:change-user-password', ['username' => 'user', 'new-password' => 'changed']);

        $this->assertNotSame($oldHash, $this->refresh($this->data['user'])->getPassword());
    }

    public function testRemoveTheme()
    {
        $this->runCommand('app:remove-theme', ['id' => $this->data['child']->getId()]);

        $this->em->clear();
        $this->assertNull($this->em->getRepository(Theme::class)->findOneBySlug('theme-enfant'));
    }
}
