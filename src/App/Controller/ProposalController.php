<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Proposal;
use App\Form\Proposal\EditProposalType;
use App\Form\Proposal\EditMotivationProposalType;

class ProposalController extends Controller
{
    public function showAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Proposal:show_proposal.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request, $themeSlug)
    {
        $theme = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($themeSlug);

        if(null === $theme) {
            throw $this->createNotFoundException();
        }

        $proposal = new Proposal();
        $form     = $this->createForm(EditProposalType::class, $proposal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proposal
                ->setAuthor($this->getUser())
                ->setTheme($theme)
                ->snapshot()
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
            ]));
        }

        return $this->render('App:Proposal:add_proposal.html.twig', [
            'proposal' => $proposal,
            'theme'    => $theme,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, $slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null ===  $proposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('edit', $proposal);

        $form            = $this->createForm(EditProposalType::class, $proposal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proposal
                ->incrementVersionNumber()
                ->snapshot()
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
             ]));
        }

        return $this->render('App:Proposal:edit_proposal.html.twig', [
            'proposal' => $proposal,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $title     = $proposal->getTitle();
        $themeSlug = $proposal->getTheme()->getSlug();

        $em = $this->getDoctrine()->getManager();
        $em->remove($proposal);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The proposal '.$title.' has been removed');

        return $this->redirect($this->generateUrl('theme_show', [
            'slug' => $themeSlug,
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function supportAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('neutral', $proposal);

        $proposal->addSupporter($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous soutenez cette proposition');

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeSupportAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('supporter', $proposal);

        $proposal->removeSupporter($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous ne soutenez plus cette proposition');

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function opposeAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('neutral', $proposal);

        $proposal->addOpponent($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous contestez cette proposition');

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeOpposeAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('opponent', $proposal);

        $proposal->removeOpponent($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous ne contestez plus cette proposition');

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }
}
