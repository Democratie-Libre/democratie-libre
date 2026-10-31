<?php

namespace Tests\Functional;

use App\Entity\Theme;
use Tests\AppTestCase;

class ThemeTest extends AppTestCase
{
    public function testAdminCreatesARootTheme()
    {
        $this->logIn('admin');
        $this->submitForm('/theme/addroot', ['title' => 'Nouvelle racine', 'abstract' => 'Un resume.']);

        $this->assertRedirectsTo('/theme/show_proposals/nouvelle-racine');
        $theme = $this->em->getRepository(Theme::class)->findOneBySlug('nouvelle-racine');
        $this->assertNotNull($theme);
        $this->assertNull($theme->getParent());
    }

    public function testAdminCreatesAChildTheme()
    {
        $this->logIn('admin');
        $slug = $this->data['theme']->getSlug();
        $this->submitForm('/theme/add/'.$slug, ['title' => 'Sous theme', 'abstract' => 'Un resume.']);

        $this->assertRedirectsTo('/theme/show_proposals/sous-theme');
        $child = $this->em->getRepository(Theme::class)->findOneBySlug('sous-theme');
        $this->assertSame($slug, $child->getParent()->getSlug());
    }

    public function testInvalidThemeIsNotCreated()
    {
        $this->logIn('admin');
        $this->submitForm('/theme/addroot', ['title' => '', 'abstract' => '']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(2, $this->em->getRepository(Theme::class)->findAll());
    }

    public function testAdminEditsATheme()
    {
        $this->logIn('admin');
        $this->submitForm('/theme/edit/'.$this->data['theme']->getSlug(), ['title' => 'Titre modifie']);

        $this->assertRedirectsTo('/theme/show_proposals/titre-modifie');
        $this->assertSame('Titre modifie', $this->refresh($this->data['theme'])->getTitle());
    }

    public function testAdminMovesAChildThemeToRoot()
    {
        $this->logIn('admin');
        $this->submitForm('/theme/move/'.$this->data['child']->getSlug(), ['parent' => '']);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->assertNull($this->refresh($this->data['child'])->getParent());
    }

    public function testThemeWithProposalsCannotBeDeleted()
    {
        $this->logIn('admin');
        $this->client->request('GET', '/theme/delete/'.$this->data['theme']->getSlug());

        $this->assertRedirectsTo('/theme/show_proposals/'.$this->data['theme']->getSlug());
        $this->assertNotNull($this->em->getRepository(Theme::class)->findOneBySlug($this->data['theme']->getSlug()));
    }

    public function testEmptyThemeIsDeletedAndItsParentIsDisplayed()
    {
        $this->logIn('admin');
        $this->client->request('GET', '/theme/delete/'.$this->data['child']->getSlug());

        $this->assertRedirectsTo('/theme/show_proposals/'.$this->data['theme']->getSlug());
        $this->assertNull($this->em->getRepository(Theme::class)->findOneBySlug($this->data['child']->getSlug()));
    }

    public function testNonAdminCannotCreateATheme()
    {
        $this->logIn('user');
        $this->client->request('GET', '/theme/addroot');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }
}
