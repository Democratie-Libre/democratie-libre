<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\ProposalDraft;
use App\Entity\Proposal;
use App\Form\EditProposalDraftMainAuthorType;
use App\Form\EditProposalDraftType;
use App\Form\AddThemeProposalType;

class ProposalDraftController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showAction($slug)
    {
        $proposalDraft = $this->getDoctrine()->getRepository('App:ProposalDraft')->findOneBySlug($slug);

        if (null === $proposalDraft) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('view', $proposalDraft)) {
            throw new AccessDeniedException();
        }

        return $this->render('App:ProposalDraft:show.html.twig', [
            'proposalDraft' => $proposalDraft,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request)
    {
        $proposalDraft = new ProposalDraft();
        $form          = $this->createForm(new EditProposalDraftType(), $proposalDraft);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $proposalDraft->setMainAuthor($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $proposalDraft->setMainAuthor($this->getUser());
            $em->persist($proposalDraft);
            $em->flush();

            return $this->redirect($this->generateUrl('proposaldraft_show', [
                'slug' => $proposalDraft->getSlug(),
            ]));
        }

        return $this->render('App:ProposalDraft:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction($slug)
    {
        $proposalDraft = $this->getDoctrine()->getRepository('App:ProposalDraft')->findOneBySlug($slug);

        if (null === $proposalDraft) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('delete', $proposalDraft)) {
            throw new AccessDeniedException();
        }

        $title = $proposalDraft->getTitle();
        $em    = $this->getDoctrine()->getManager();
        $em->remove($proposalDraft);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'Le brouillon de proposition '.$title.' a bien été supprimé');

        return $this->redirect($this->generateUrl('profile'));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, $slug)
    {
        $proposalDraft = $this->getDoctrine()->getRepository('App:ProposalDraft')->findOneBySlug($slug);

        if (null === $proposalDraft) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('edit', $proposalDraft)) {
            throw new AccessDeniedException();
        }

        if ($this->get('security.context')->isGranted('main_author', $proposalDraft)) {
            $form = $this->createForm(new EditProposalDraftMainAuthorType(), $proposalDraft);
        } else {
            $form = $this->createForm(new EditProposalDraftType(), $proposalDraft);
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($proposalDraft);
            $em->flush();

            return $this->redirect($this->generateUrl('proposaldraft_show', [
                'slug' => $proposalDraft->getSlug(),
            ]));
        }

        return $this->render('App:ProposalDraft:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function publishAction(Request $request, $slug)
    {
        $proposalDraft = $this->getDoctrine()->getRepository('App:ProposalDraft')->findOneBySlug($slug);

        if (null === $proposalDraft) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('publish', $proposalDraft)) {
            throw new AccessDeniedException();
        }

        $proposal = new Proposal();
        $proposal->editFromDraft($proposalDraft);
        $form = $this->createForm(new AddThemeProposalType(), $proposal);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $title = $proposalDraft->getTitle();

            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->remove($proposalDraft);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'La proposition '.$title.' a bien été publiée !');

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposal->getSlug(),
            ]));
        }

        return $this->render('App:ProposalDraft:publish.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeSideAuthorAction($slug)
    {
        $proposalDraft = $this->getDoctrine()->getRepository('App:ProposalDraft')->findOneBySlug($slug);

        if (null === $proposalDraft) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('side_author', $proposalDraft)) {
            throw new AccessDeniedException();
        }

        $proposalDraft->removeSideAuthor($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposalDraft);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous ne faites plus partie des auteurs secondaires de ce brouillon de proposition');

        return $this->redirect($this->generateUrl('proposaldraft_show', [
            'slug' => $proposalDraft->getSlug(),
        ]));
    }
}
