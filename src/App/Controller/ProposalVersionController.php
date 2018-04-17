<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ProposalVersionController extends Controller
{
    public function showAction($slug)
    {
        $proposalVersion = $this->getDoctrine()->getRepository('App:ProposalVersion')->findOneBySlug($slug);

        if (null === $proposalVersion) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:ProposalVersion:show_proposal_version.html.twig', [
            'proposalVersion' => $proposalVersion,
        ]);
    }

    public function previousVersionAction($slug)
    {
        $repository      = $this->getDoctrine()->getRepository('App:ProposalVersion');
        $proposalVersion = $repository->findOneBySlug($slug);

        if (null === $proposalVersion) {
            throw $this->createNotFoundException();
        }

        if ($proposalVersion->isFirstVersion()) {
            throw $this->createNotFoundException('This is the first version !');
        }

        $previousVersion = $repository->findPreviousVersion($proposalVersion);

        return $this->redirect($this->generateUrl('proposal_version_show', [
            'slug' => $previousVersion->getSlug(),
        ]));
    }

    public function nextVersionAction($slug)
    {
        $repository      = $this->getDoctrine()->getRepository('App:ProposalVersion');
        $proposalVersion = $repository->findOneBySlug($slug);

        if (null === $proposalVersion) {
            throw $this->createNotFoundException();
        }

        if ($proposalVersion->isLastVersion()) {
            throw $this->createNotFoundException('This is the last version !');
        }

        $nextVersion = $repository->findNextVersion($proposalVersion);

        return $this->redirect($this->generateUrl('proposal_version_show', [
            'slug' => $nextVersion->getSlug(),
        ]));
    }
}
