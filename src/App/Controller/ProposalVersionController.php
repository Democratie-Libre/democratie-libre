<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ProposalVersionController extends Controller
{
    public function showAction($slug)
    {
        $version = $this->getDoctrine()->getRepository('App:ProposalVersion')->findOneBySlug($slug);

        if (null === $version) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:ProposalVersion:show.html.twig', [
            'version' => $version,
        ]);
    }

    public function previousAction($slug)
    {
        $repository = $this->getDoctrine()->getRepository('App:ProposalVersion');
        $version    = $repository->findOneBySlug($slug);

        if ((null === $version) or ($this->get('security.context')->isGranted('first_version', $version))) {
            throw $this->createNotFoundException();
        }

        $versionNumber   = $version->getVersionNumber();
        $proposal        = $version->getProposal();
        $previousVersion = $repository->findOneBy([
            'proposal'      => $proposal,
            'versionNumber' => $versionNumber - 1,
        ]);

        return $this->redirect($this->generateUrl('proposalversion_show', [
            'slug' => $previousVersion->getSlug(),
        ]));
    }

    public function nextAction($slug)
    {
        $repository = $this->getDoctrine()->getRepository('App:ProposalVersion');
        $version    = $repository->findOneBySlug($slug);

        if (null === $version) {
            throw $this->createNotFoundException();
        }

        $proposal = $version->getProposal();

        if ($this->get('security.context')->isGranted('last_version', $version)) {
            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
            ]));
        }

        $versionNumber = $version->getVersionNumber();
        $nextVersion   = $repository->findOneBy([
            'proposal'      => $proposal,
            'versionNumber' => $versionNumber + 1,
        ]);

        return $this->redirect($this->generateUrl('proposalversion_show', [
            'slug' => $nextVersion->getSlug(),
        ]));
    }
}
