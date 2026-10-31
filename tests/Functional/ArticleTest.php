<?php

namespace Tests\Functional;

use App\Entity\Article;
use Tests\AppTestCase;

class ArticleTest extends AppTestCase
{
    public function testAuthorAddsAnArticle()
    {
        $this->logIn('user');
        $this->submitForm('/article/add/'.$this->data['proposal']->getSlug(), [
            'title'      => 'Deuxieme article',
            'content'    => 'Le contenu.',
            'motivation' => 'La motivation.',
        ]);

        $this->assertRedirectsTo('/article/show/deuxieme-article');
        $article = $this->em->getRepository(Article::class)->findOneBySlug('deuxieme-article');
        $this->assertSame(2, $article->getNumber());
        $this->assertSame(Article::PUBLISHED, $article->getStatus());
    }

    public function testAnotherUserCannotAddAnArticle()
    {
        $this->logIn('other');
        $this->client->request('GET', '/article/add/'.$this->data['proposal']->getSlug());

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAuthorEditsAnArticleAndVersionsAreRecorded()
    {
        $articleVersionBefore  = $this->data['article']->getVersionNumber();
        $proposalVersionBefore = $this->data['proposal']->getVersionNumber();
        $snapshotsBefore       = count($this->data['article']->getVersioning());

        $this->logIn('user');
        $this->submitForm('/article/edit/'.$this->data['article']->getSlug(), ['content' => 'Nouveau contenu.']);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $article = $this->refresh($this->data['article']);
        $this->assertSame('Nouveau contenu.', $article->getContent());
        $this->assertSame($articleVersionBefore + 1, $article->getVersionNumber());
        $this->assertCount($snapshotsBefore + 1, $article->getVersioning());
        $this->assertSame($proposalVersionBefore + 1, $article->getProposal()->getVersionNumber());
    }

    public function testAuthorRemovesAnArticle()
    {
        $slug = $this->data['article']->getSlug();
        $this->logIn('user');
        $this->submitForm('/article/remove/'.$slug, ['removing_explanation' => 'Redondant.']);

        $this->assertRedirectsTo('/article/show/'.$slug);
        $article = $this->refresh($this->data['article']);
        $this->assertSame(Article::REMOVED, $article->getStatus());
        $this->assertSame('Redondant.', $article->getRemovingExplanation());
    }
}
