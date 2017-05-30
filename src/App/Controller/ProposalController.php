<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Proposal;
use App\Entity\OldProposal;
use App\Form\Proposal\EditProposalType;
use App\Form\Proposal\PublishProposalType;

class ProposalController extends Controller
{
    public function showAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Proposal:show.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request)
    {
        $proposal = new Proposal();
        $form     = $this->createForm(new EditProposalType(), $proposal);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $proposal->setAuthor($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
            ]));
        }

        return $this->render('App:Proposal:edit.html.twig', [
            'form' => $form->createView(),
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

        if (false === $this->get('security.context')->isGranted('edit', $proposal)) {
            throw new AccessDeniedException();
        }

        $oldProposal = new OldProposal($proposal);
        $form        = $this->createForm(new EditProposalType(), $proposal);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $proposal
                ->addToHistory($oldProposal)
                ->setVersionNumber($proposal->getVersionNumber() + 1)
            ;
            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
             ]));
        }

        return $this->render('App:Proposal:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function publishAction(Request $request, $slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null ===  $proposal) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('author', $proposal)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new PublishProposalType(), $proposal);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $proposal->setIsPublished(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
             ]));
        }

        return $this->render('App:Proposal:publish.html.twig', [
            'form' => $form->createView(),
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

        $title = $proposal->getTitle();
        $em    = $this->getDoctrine()->getManager();
        $em->remove($proposal);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The proposal '.$title.' has been suppressed');

        if ($proposal->isPublished()) {
            $themeSlug = $proposal->getTheme()->getSlug();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $themeSlug,
            ]));
        }

        return $this->redirect($this->generateUrl('profile'));
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

        $isNeutral = $this->get('security.context')->isGranted('neutral', $proposal);

        if (false === $isNeutral) {
            throw new AccessDeniedException();
        }

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

        $isSupporter = $this->get('security.context')->isGranted('supporter', $proposal);

        if (false === $isSupporter) {
            throw new AccessDeniedException();
        }

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

        $isNeutral = $this->get('security.context')->isGranted('neutral', $proposal);

        if (false === $isNeutral) {
            throw new AccessDeniedException();
        }

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

        $isOpponent = $this->get('security.context')->isGranted('opponent', $proposal);

        if ($isOpponent) {
            $proposal->removeOpponent($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();
            $this->get('session')->getFlashBag()->add('info', 'Vous ne contestez plus cette proposition');

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
            ]));
        }

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    public function showDiscussionsAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $discussions = $this->getDoctrine()->getRepository('App:Discussion')->findByProposal($proposal);

        return $this->render('App:Proposal:discussions.html.twig', [
            'discussions' => $discussions,
        ]);
    }
}
