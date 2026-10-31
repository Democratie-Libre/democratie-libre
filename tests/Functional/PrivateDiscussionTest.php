<?php

namespace Tests\Functional;

use App\Entity\Post;
use App\Entity\PrivateDiscussion;
use Tests\AppTestCase;

class PrivateDiscussionTest extends AppTestCase
{
    public function testUserOpensAPrivateDiscussionFromAPostOfSomeoneElse()
    {
        $this->logIn('other');
        $this->client->request('GET', '/post/open_private_discussion/'.$this->data['post']->getId());

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->assertStringStartsWith('/private_discussion/show/', parse_url($this->client->getResponse()->headers->get('Location'), PHP_URL_PATH));

        // the dataset already holds one private discussion
        $discussions = $this->em->getRepository(PrivateDiscussion::class)->findAll();
        $this->assertCount(2, $discussions);
        $new = $discussions[1];
        $this->assertSame('other', $new->getAdmin()->getUsername());
        $this->assertCount(2, $new->getMembers());
    }

    public function testMembersCanSeeAPrivateDiscussionButNotOutsiders()
    {
        $slug = $this->data['private_discussion']->getSlug();

        $this->createUser('outsider');
        $this->logIn('outsider');
        $this->client->request('GET', '/private_discussion/show/'.$slug);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testMemberPostsAMessage()
    {
        $slug = $this->data['private_discussion']->getSlug();
        $this->logIn('other');
        $this->submitForm('/private_discussion/show/'.$slug, ['content' => 'Message prive.']);

        $this->assertRedirectsTo('/private_discussion/show/'.$slug);
        $this->assertCount(1, $this->em->getRepository(Post::class)->findBy(['content' => 'Message prive.']));
    }

    public function testAdminOfTheDiscussionEditsItsTitle()
    {
        $this->logIn('user');
        $this->submitForm('/private_discussion/edit/'.$this->data['private_discussion']->getSlug(), ['title' => 'Nouveau titre']);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->assertSame('Nouveau titre', $this->refresh($this->data['private_discussion'])->getTitle());
    }

    public function testMemberLocksThePrivateDiscussion()
    {
        $slug = $this->data['private_discussion']->getSlug();
        $this->logIn('other');
        $this->client->request('GET', '/private_discussion/lock/'.$slug);

        $this->assertRedirectsTo('/private_discussion/show/'.$slug);
        $this->assertTrue($this->refresh($this->data['private_discussion'])->isLocked());
    }

    public function testNoMessageCanBePostedInALockedPrivateDiscussion()
    {
        $this->data['private_discussion']->setLocked(true);
        $this->em->flush();

        $this->logIn('user');
        $crawler = $this->client->request('GET', '/private_discussion/show/'.$this->data['private_discussion']->getSlug());

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(0, $crawler->filter('button[name$="[save]"]'));
    }
}
