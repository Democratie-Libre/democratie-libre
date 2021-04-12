<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\PublicDiscussion;
use App\Entity\Proposal;

class GlobalController extends Controller
{
    public function indexAction()
    {
        return $this->render('App:Global:index.html.twig');
    }

    public function rootsAction()
    {
        $roots = $this->getDoctrine()->getRepository('App:Theme')->findRoots();

        return $this->render('App:Global:roots.html.twig', ['roots' => $roots]);
    }

    public function globalRoomAction()
    {
        $discussions = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findByType(PublicDiscussion::GLOBAL_DISCUSSION);

        return $this->render('App:Global:global_room.html.twig', ['discussions' => $discussions]);
    }

    public function projectAction()
    {
        return $this->render('App:Global:project.html.twig');
    }

    public function contactAction()
    {
        return $this->render('App:Global:contact.html.twig');
    }

    public function removedProposalsAction()
    {
        $removed_proposals = $this->getDoctrine()->getRepository('App:Proposal')->findByStatus(Proposal::REMOVED);

        return $this->render('App:Global:removed_proposals.html.twig', ['removed_proposals' => $removed_proposals]);
    }
}
