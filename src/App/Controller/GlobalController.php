<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GlobalController extends Controller
{
    public function indexAction()
    {
        $roots = $this->getDoctrine()->getRepository('App:Theme')->findRoots();

        return $this->render('App:Global:index.html.twig', ['roots' => $roots]);
    }
}
