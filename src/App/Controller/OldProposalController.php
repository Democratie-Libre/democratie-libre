<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class OldProposalController extends Controller
{
    public function showAction($slug)
    {
        $oldProposal = $this->getDoctrine()->getRepository('App:OldProposal')->findOneBySlug($slug);

        if (null === $oldProposal) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:OldProposal:show.html.twig', [
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
