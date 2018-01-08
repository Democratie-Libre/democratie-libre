<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

class OldProposalController extends Controller
{
    public function showAction($slug)
    {
        $oldProposal = $this->getDoctrine()->getRepository('App:OldProposal')->findOneBySlug($slug);

        if (null === $oldProposal) {
            throw $this->createNotFoundException();
        }

        $engine = new MarkdownEngine\MichelfMarkdownEngine();
        $twig   = $this->container->get('twig');
        $twig->addExtension(new MarkdownExtension($engine));

        return $this->render('App:OldProposal:show_old_proposal.html.twig', [
            'oldProposal' => $oldProposal,
        ]);
    }

    public function previousVersionAction($slug)
    {
        $repository  = $this->getDoctrine()->getRepository('App:OldProposal');
        $oldProposal = $repository->findOneBySlug($slug);

        if (null === $oldProposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('not_first_version', $oldProposal, 'This is the oldest version !');

        $previousVersion = $repository->findPreviousVersion($oldProposal);

        return $this->redirect($this->generateUrl('old_proposal_show', [
            'slug' => $previousVersion->getSlug(),
        ]));
    }

    public function nextVersionAction($slug)
    {
        $repository  = $this->getDoctrine()->getRepository('App:OldProposal');
        $oldProposal = $repository->findOneBySlug($slug);

        if (null === $oldProposal) {
            throw $this->createNotFoundException();
        }

        if ($this->isGranted('last_version', $oldProposal)) {
            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $oldProposal->getRecordedProposal()->getSlug(),
            ]));
        }

        $nextVersion = $repository->findNextVersion($oldProposal);

        return $this->redirect($this->generateUrl('old_proposal_show', [
            'slug' => $nextVersion->getSlug(),
        ]));
    }
}
