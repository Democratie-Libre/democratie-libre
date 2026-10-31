<?php

namespace App\Controller;

use App\Entity\Theme;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\PublicDiscussion;
use App\Entity\Proposal;

class GlobalController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function indexAction()
    {
        return $this->render('@App/Global/index.html.twig');
    }

    public function rootsAction()
    {
        $roots = $this->doctrine->getRepository(Theme::class)->findRoots();

        return $this->render('@App/Global/roots.html.twig', ['roots' => $roots]);
    }

    public function globalRoomAction()
    {
        $discussions = $this->doctrine->getRepository(PublicDiscussion::class)->findByType(PublicDiscussion::GLOBAL_DISCUSSION);

        return $this->render('@App/Global/global_room.html.twig', ['discussions' => $discussions]);
    }

    public function projectAction()
    {
        return $this->render('@App/Global/project.html.twig');
    }

    public function contactAction()
    {
        return $this->render('@App/Global/contact.html.twig');
    }

    public function removedProposalsAction()
    {
        $removedProposals = $this->doctrine->getRepository(Proposal::class)->findByStatus(Proposal::REMOVED);

        return $this->render('@App/Global/removed_proposals.html.twig', ['removed_proposals' => $removedProposals]);
    }
}
