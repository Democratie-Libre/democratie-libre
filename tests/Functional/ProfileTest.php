<?php

namespace Tests\Functional;

use Tests\AppTestCase;

class ProfileTest extends AppTestCase
{
    /**
     * Characterization: the email is saved, but the redirection targets the
     * "profile" route, which does not exist, so the user gets an error page.
     */
    public function testEmailEditIsSavedButRedirectionIsBroken()
    {
        $this->logIn('user');
        try {
            $this->submitForm('/email/edit', ['email' => 'nouveau@example.com']);
            $status = $this->client->getResponse()->getStatusCode();
        } catch (\Throwable $e) {
            $status = 500;
        }

        $this->assertSame(500, $status);
        $this->assertSame('nouveau@example.com', $this->refresh($this->data['user'])->getEmail());
    }

    public function testPasswordEditChangesThePasswordUsedToLogIn()
    {
        $this->logIn('user');
        try {
            $this->submitForm('/password/edit', ['plainPassword' => ['first' => 'secret42', 'second' => 'secret42']]);
        } catch (\Throwable $e) {
            // same broken "profile" redirection as above
        }
        $this->client->request('GET', '/logout');

        $crawler = $this->client->request('GET', '/login');
        $this->client->submit($crawler->selectButton('Connexion')->form(['_username' => 'user', '_password' => 'secret42']));

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->client->request('GET', '/profile/proposals');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testRegisterPageDisplaysTheForm()
    {
        $crawler = $this->client->request('GET', '/register');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('input[name$="[username]"]')->count());
    }
}
