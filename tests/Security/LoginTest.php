<?php

namespace Tests\Security;

use Tests\AppTestCase;

class LoginTest extends AppTestCase
{
    public function testLoginPageIsDisplayed()
    {
        $this->client->request('GET', '/login');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanLogInAndOut()
    {
        $this->logIn('user');
        $this->client->request('GET', '/profile/proposals');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/logout');
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->request('GET', '/profile/proposals');
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    public function testWrongPasswordIsRejected()
    {
        $crawler = $this->client->request('GET', '/login');
        $form    = $crawler->selectButton('Connexion')->form([
            '_username' => 'user',
            '_password' => 'wrong',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertStringContainsString('Identifiants invalides', $this->client->getResponse()->getContent());
    }
}
