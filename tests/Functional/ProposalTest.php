<?php

namespace Tests\Functional;

use App\Entity\Proposal;
use Tests\AppTestCase;

class ProposalTest extends AppTestCase
{
    public function testUserCreatesAProposal()
    {
        $this->logIn('other');
        $this->submitForm('/proposal/add/'.$this->data['theme']->getSlug(), [
            'title'      => 'Ma proposition',
            'abstract'   => 'Un resume.',
            'motivation' => 'Une motivation.',
        ]);

        $this->assertRedirectsTo('/proposal/show_motivation/ma-proposition');
        $proposal = $this->em->getRepository(Proposal::class)->findOneBySlug('ma-proposition');
        $this->assertSame('other', $proposal->getAuthor()->getUsername());
        $this->assertSame(Proposal::PUBLISHED, $proposal->getStatus());
        $this->assertCount(1, $proposal->getVersioning());
    }

    public function testInvalidProposalIsNotCreated()
    {
        $this->logIn('other');
        $this->submitForm('/proposal/add/'.$this->data['theme']->getSlug(), ['title' => '']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $this->em->getRepository(Proposal::class)->findAll());
    }

    public function testAuthorEditsAProposalAndANewVersionIsRecorded()
    {
        $versionBefore = $this->data['proposal']->getVersionNumber();
        $snapshotsBefore = count($this->data['proposal']->getVersioning());

        $this->logIn('user');
        $this->submitForm('/proposal/edit/'.$this->data['proposal']->getSlug(), ['abstract' => 'Resume modifie.']);

        $proposal = $this->refresh($this->data['proposal']);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->assertSame('Resume modifie.', $proposal->getAbstract());
        $this->assertSame($versionBefore + 1, $proposal->getVersionNumber());
        $this->assertCount($snapshotsBefore + 1, $proposal->getVersioning());
    }

    public function testSupportThenRemoveSupport()
    {
        $slug = $this->data['proposal']->getSlug();
        $this->logIn('other');

        $this->client->request('GET', '/proposal/support/'.$slug);
        $this->assertRedirectsTo('/proposal/show_motivation/'.$slug);
        $this->assertCount(1, $this->refresh($this->data['proposal'])->getSupporters());

        $this->client->request('GET', '/proposal/removesupport/'.$slug);
        $this->assertRedirectsTo('/proposal/show_motivation/'.$slug);
        $this->assertCount(0, $this->refresh($this->data['proposal'])->getSupporters());
    }

    public function testOpposeThenRemoveOppose()
    {
        $slug = $this->data['proposal']->getSlug();
        $this->logIn('other');

        $this->client->request('GET', '/proposal/oppose/'.$slug);
        $this->assertRedirectsTo('/proposal/show_motivation/'.$slug);
        $this->assertCount(1, $this->refresh($this->data['proposal'])->getOpponents());

        $this->client->request('GET', '/proposal/removeoppose/'.$slug);
        $this->assertRedirectsTo('/proposal/show_motivation/'.$slug);
        $this->assertCount(0, $this->refresh($this->data['proposal'])->getOpponents());
    }

    public function testCannotSupportAndOpposeTheSameProposal()
    {
        $slug = $this->data['proposal']->getSlug();
        $this->logIn('other');

        $this->client->request('GET', '/proposal/support/'.$slug);
        $this->client->request('GET', '/proposal/oppose/'.$slug);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAuthorLocksAProposalThenAdminDeletesIt()
    {
        $slug = $this->data['proposal']->getSlug();
        $this->logIn('user');
        $this->submitForm('/proposal/lock/'.$slug, ['locking_explanation' => 'Plus d\'actualite.']);

        $this->assertRedirectsTo('/proposal/show_motivation/'.$slug);
        $this->assertSame(Proposal::LOCKED, $this->refresh($this->data['proposal'])->getStatus());

        $this->client->request('GET', '/logout');
        $this->logIn('admin');
        $this->client->request('GET', '/proposal/delete/'.$slug);

        $this->assertRedirectsTo('/roots');
        $this->assertCount(0, $this->em->getRepository(Proposal::class)->findAll());
    }

    public function testAdminMovesAProposalToAnotherTheme()
    {
        $versionBefore = $this->data['proposal']->getVersionNumber();

        $this->logIn('admin');
        $this->submitForm('/proposal/move/'.$this->data['proposal']->getSlug(), [
            'theme' => $this->data['child']->getId(),
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $proposal = $this->refresh($this->data['proposal']);
        $this->assertSame($this->data['child']->getId(), $proposal->getTheme()->getId());
        $this->assertSame($versionBefore + 1, $proposal->getVersionNumber());
    }
}
