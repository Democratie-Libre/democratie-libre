<?php

namespace App\Controller;

use App\Entity\ProposalVersion;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProposalVersionController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function showAction($slug)
    {
        $proposalVersion = $this->doctrine->getRepository(ProposalVersion::class)->findOneBySlug($slug);

        if (null === $proposalVersion) {
            throw $this->createNotFoundException();
        }

        return $this->render('@App/ProposalVersion/show_proposal_version.html.twig', [
            'proposalVersion' => $proposalVersion,
        ]);
    }

    public function previousVersionAction($slug)
    {
        $repository      = $this->doctrine->getRepository(ProposalVersion::class);
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
        $repository      = $this->doctrine->getRepository(ProposalVersion::class);
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
