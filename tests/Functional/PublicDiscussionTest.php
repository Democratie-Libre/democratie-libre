<?php

namespace Tests\Functional;

use App\Entity\Post;
use App\Entity\PublicDiscussion;
use Tests\AppTestCase;

class PublicDiscussionTest extends AppTestCase
{
    public function testUserOpensAGlobalDiscussion()
    {
        $this->logIn('other');
        $this->submitForm('/public_discussion/add_to_global_discussions', ['title' => 'Un sujet global']);

        $this->assertRedirectsTo('/public_discussion/show/un-sujet-global');
        $discussion = $this->em->getRepository(PublicDiscussion::class)->findOneBySlug('un-sujet-global');
        $this->assertTrue($discussion->isGlobalDiscussion());
        $this->assertTrue($discussion->hasFollower($this->em->getRepository(\App\Entity\User::class)->findOneByUsername('other')));
    }

    public function testUserOpensADiscussionOnThemeProposalAndArticle()
    {
        $this->logIn('other');

        $this->submitForm('/public_discussion/add_to_theme/'.$this->data['theme']->getSlug(), ['title' => 'Sujet theme']);
        $this->assertRedirectsTo('/public_discussion/show/sujet-theme');

        $this->submitForm('/public_discussion/add_to_proposal/'.$this->data['proposal']->getSlug(), ['title' => 'Sujet proposition']);
        $this->assertRedirectsTo('/public_discussion/show/sujet-proposition');

        $this->submitForm('/public_discussion/add_to_article/'.$this->data['article']->getSlug(), ['title' => 'Sujet article']);
        $this->assertRedirectsTo('/public_discussion/show/sujet-article');

        $repository = $this->em->getRepository(PublicDiscussion::class);
        $this->assertTrue($repository->findOneBySlug('sujet-theme')->isThemeDiscussion());
        $this->assertTrue($repository->findOneBySlug('sujet-proposition')->isProposalDiscussion());
        $this->assertTrue($repository->findOneBySlug('sujet-article')->isArticleDiscussion());
    }

    public function testAnonymousCannotOpenADiscussion()
    {
        $this->client->request('GET', '/public_discussion/add_to_global_discussions');

        $this->assertRedirectsTo('/login');
    }

    public function testUserPostsAMessage()
    {
        $slug = $this->data['discussion']->getSlug();
        $this->logIn('other');
        $this->submitForm('/public_discussion/show/'.$slug, ['content' => 'Mon avis.']);

        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $posts = $this->em->getRepository(Post::class)->findBy(['content' => 'Mon avis.']);
        $this->assertCount(1, $posts);
        $this->assertSame('other', $posts[0]->getAuthor()->getUsername());
    }

    public function testFollowAndStopFollowing()
    {
        $slug = $this->data['discussion']->getSlug();
        $this->logIn('other');

        $this->client->request('GET', '/public_discussion/follow/'.$slug);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertCount(1, $this->refresh($this->data['discussion'])->getFollowers());

        $this->client->request('GET', '/public_discussion/stop_following/'.$slug);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertCount(0, $this->refresh($this->data['discussion'])->getFollowers());
    }

    public function testAdminLocksAndUnlocksADiscussion()
    {
        $slug = $this->data['discussion']->getSlug();
        $this->logIn('admin');

        $this->client->request('GET', '/public_discussion/lock/'.$slug);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertTrue($this->refresh($this->data['discussion'])->isLocked());

        $this->client->request('GET', '/public_discussion/unlock/'.$slug);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertFalse($this->refresh($this->data['discussion'])->isLocked());
    }

    public function testNoFormIsDisplayedOnALockedDiscussion()
    {
        $this->data['discussion']->setLocked(true);
        $this->em->flush();

        $this->logIn('other');
        $crawler = $this->client->request('GET', '/public_discussion/show/'.$this->data['discussion']->getSlug());

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(0, $crawler->filter('button[name$="[save]"]'));
    }

    public function testAdminEditsADiscussion()
    {
        $this->logIn('admin');
        $this->submitForm('/public_discussion/edit/'.$this->data['discussion']->getSlug(), ['title' => 'Titre corrige']);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->assertSame('Titre corrige', $this->refresh($this->data['discussion'])->getTitle());
    }

    public function testAdminMovesADiscussion()
    {
        $slug = $this->data['discussion']->getSlug();
        $this->logIn('admin');

        $this->client->request('GET', '/public_discussion/move_to_global/'.$slug);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertTrue($this->refresh($this->data['discussion'])->isGlobalDiscussion());

        $this->submitForm('/public_discussion/move_to_proposal/'.$slug, ['proposal' => $this->data['proposal']->getId()]);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertTrue($this->refresh($this->data['discussion'])->isProposalDiscussion());

        $this->submitForm('/public_discussion/move_to_article/'.$slug, ['article' => $this->data['article']->getId()]);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertTrue($this->refresh($this->data['discussion'])->isArticleDiscussion());

        $this->submitForm('/public_discussion/move_to_theme/'.$slug, ['theme' => $this->data['child']->getId()]);
        $this->assertRedirectsTo('/public_discussion/show/'.$slug);
        $this->assertTrue($this->refresh($this->data['discussion'])->isThemeDiscussion());
    }

    public function testAdminDeletesAThemeDiscussion()
    {
        $this->logIn('admin');
        $this->client->request('GET', '/public_discussion/delete/'.$this->data['discussion']->getSlug());

        $this->assertRedirectsTo('/theme/show_proposals/'.$this->data['theme']->getSlug());
        $this->assertNull($this->em->getRepository(PublicDiscussion::class)->findOneBySlug($this->data['discussion']->getSlug()));
    }

    public function testAdminDeletesAGlobalDiscussion()
    {
        $this->logIn('admin');
        $this->client->request('GET', '/public_discussion/delete/'.$this->data['global_discussion']->getSlug());

        $this->assertRedirectsTo('/global_room');
    }

    public function testAdminDeletesAnArticleDiscussion()
    {
        $discussion = PublicDiscussion::createArticleDiscussion($this->data['article'])->setTitle('Sur article');
        $this->em->persist($discussion);
        $this->em->flush();

        $this->logIn('admin');
        $this->client->request('GET', '/public_discussion/delete/'.$discussion->getSlug());

        $this->assertRedirectsTo('/article/show/'.$this->data['article']->getSlug());
    }

    /**
     * The redirection targets the "proposal_show" route, which does not exist:
     * the discussion is removed but the user gets an error page.
     */
    public function testDeletingAProposalDiscussionIsBrokenByAMissingRoute()
    {
        $discussion = PublicDiscussion::createProposalDiscussion($this->data['proposal'])->setTitle('Sur proposition');
        $this->em->persist($discussion);
        $this->em->flush();

        $this->logIn('admin');
        try {
            $this->client->request('GET', '/public_discussion/delete/'.$discussion->getSlug());
            $status = $this->client->getResponse()->getStatusCode();
        } catch (\Throwable $e) {
            $status = 500;
        }

        $this->assertSame(500, $status);
    }

    public function testAdminDeletesAPostOfAPublicDiscussion()
    {
        $this->logIn('admin');
        $this->client->request('GET', '/post/delete/'.$this->data['post']->getId());

        $this->assertRedirectsTo('/public_discussion/show/'.$this->data['discussion']->getSlug());
        $this->assertCount(0, $this->em->getRepository(Post::class)->findAll());
    }

    public function testAdminMovesAPostToAnotherDiscussion()
    {
        $this->logIn('admin');
        $this->submitForm('/post/move/'.$this->data['post']->getId(), [
            'discussion' => $this->idOf($this->data['global_discussion']),
        ]);

        $this->assertRedirectsTo('/public_discussion/show/'.$this->data['global_discussion']->getSlug());
        $this->assertSame(
            $this->idOf($this->data['global_discussion']),
            $this->idOf($this->refresh($this->data["post"])->getDiscussion())
        );
    }
}
