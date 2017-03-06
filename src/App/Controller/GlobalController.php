<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

    public function globalDiscussionsAction()
    {
        $discussions = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findByType('GLOBAL_DISCUSSION');

        return $this->render('App:Global:global_discussions.html.twig', ['discussions' => $discussions]);
    }
}
