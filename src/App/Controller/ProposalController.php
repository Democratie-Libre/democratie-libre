<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Proposal;
use App\Entity\ProposalVersion;
use App\Form\EditProposalType;
use App\Form\EditProposalMainAuthorType;
use App\Form\EditProposalAdminType;

class ProposalController extends Controller
{
    public function showAction(Request $request, $slug)
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
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $title     = $proposal->getTitle();
        $themeSlug = $proposal->getTheme()->getSlug();
        $em        = $this->getDoctrine()->getManager();
        $em->remove($proposal);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'La proposition '.$title.' a bien été supprimée');

        return $this->redirect($this->generateUrl('theme_show', [
            'slug' => $themeSlug,
        ]));
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

        // save the current version of the proposal
        $user    = $this->getUser();
        $version = new ProposalVersion();
        $version->edit($proposal, $user);

        $isAdmin      = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $isMainAuthor = $this->get('security.context')->isGranted('main_author', $proposal);

        if ($isAdmin) {
            $form = $this->createForm(new EditProposalAdminType(), $proposal);
        } elseif ($isMainAuthor) {
            $form = $this->createForm(new EditProposalMainAuthorType(), $proposal);
        } else {
            $form = $this->createForm(new EditProposalType(), $proposal);
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $proposal
                ->addVersion($version)
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

        $proposal->addSupportiveUser($this->getUser());
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

        $proposal->removeSupportiveUser($this->getUser());
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
            throw $this->createNotFoundException('Impossible de trouver cette proposition.');
        }

        $isNeutral = $this->get('security.context')->isGranted('neutral', $proposal);

        if (false === $isNeutral) {
            throw new AccessDeniedException();
        }

        $proposal->addOpposedUser($this->getUser());
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
            $proposal->removeOpposedUser($this->getUser());
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

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeSideAuthorAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('side_author', $proposal)) {
            throw new AccessDeniedException();
        }

        $proposal->removeSideAuthor($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous ne faites plus partie des auteurs secondaires de cette proposition');

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    public function showDiscussionsAction(Request $request, $slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null ===  $proposal) {
            throw $this->createNotFoundException();
        }

        $discussions = $this->getDoctrine()->getRepository('App:Discussion')->findProposalDiscussions($proposal);

        return $this->render('App:Proposal:discussions.html.twig', [
            'discussions' => $discussions,
        ]);
    }
}
