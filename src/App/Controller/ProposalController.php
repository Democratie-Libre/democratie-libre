<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Proposal;
use App\Form\Proposal\EditProposalType;
use App\Form\Proposal\LockProposalType;
use App\Form\SelectThemeType;
use App\Security\Authorization\Voter\ProposalVoter;

class ProposalController extends Controller
{
    public function showMotivationAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        return $this->render('App:Proposal:show_proposal_motivation.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    public function showArticlesAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        return $this->render('App:Proposal:show_proposal_articles.html.twig', [
            'proposal'        => $proposal,
            'locked_articles' => false,
        ]);
    }

    public function showRemovedArticlesAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        return $this->render('App:Proposal:show_proposal_removed_articles.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    public function showDiscussionsAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        return $this->render('App:Proposal:show_proposal_discussions.html.twig', [
            'proposal'           => $proposal,
            'locked_discussions' => False,
        ]);
    }

    public function showLockedDiscussionsAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        return $this->render('App:Proposal:show_proposal_discussions.html.twig', [
            'proposal'           => $proposal,
            'locked_discussions' => True,
        ]);
    }

    public function showVersioningAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        return $this->render('App:Proposal:show_proposal_versioning.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showAdministrationAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::SHOW_ADMIN_PANEL, $proposal);

        return $this->render('App:Proposal:show_proposal_administration.html.twig', [
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

            return $this->redirect($this->generateUrl('proposal_show_motivation', [
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
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::PUBLISHED, $proposal);
        $this->denyAccessUnlessGranted(ProposalVoter::CAN_BE_EDITED, $proposal);

        $form = $this->createForm(EditProposalType::class, $proposal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proposal
                ->incrementVersionNumber()
                ->snapshot()
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show_motivation', [
                'slug' => $proposal->getSlug(),
             ]));
        }

        return $this->render('App:Proposal:edit_proposal.html.twig', [
            'proposal' => $proposal,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function supportAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::PUBLISHED, $proposal);
        $this->denyAccessUnlessGranted(ProposalVoter::NEUTRAL, $proposal);

        $proposal->addSupporter($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous soutenez cette proposition');

        return $this->redirect($this->generateUrl('proposal_show_motivation', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeSupportAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::PUBLISHED, $proposal);
        $this->denyAccessUnlessGranted(ProposalVoter::SUPPORTER, $proposal);

        $proposal->removeSupporter($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous ne soutenez plus cette proposition');

        return $this->redirect($this->generateUrl('proposal_show_motivation', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function opposeAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::PUBLISHED, $proposal);
        $this->denyAccessUnlessGranted(ProposalVoter::NEUTRAL, $proposal);

        $proposal->addOpponent($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous contestez cette proposition');

        return $this->redirect($this->generateUrl('proposal_show_motivation', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeOpposeAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::PUBLISHED, $proposal);
        $this->denyAccessUnlessGranted(ProposalVoter::OPPONENT, $proposal);

        $proposal->removeOpponent($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($proposal);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'Vous ne contestez plus cette proposition');

        return $this->redirect($this->generateUrl('proposal_show_motivation', [
            'slug' => $proposal->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveAction(Request $request, $slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::CAN_BE_MOVED, $proposal);

        $form = $this->createForm(SelectThemeType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hostTheme = $form->get('theme')->getData();
            $proposal
                ->setTheme($hostTheme)
                ->incrementVersionNumber()
                ->snapshot()
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'The proposal has been moved in the theme '.$hostTheme->getTitle());

            return $this->redirect($this->generateUrl('proposal_show_motivation', [
                'slug' => $proposal->getSlug(),
             ]));
        }

        return $this->render('App:Proposal:move_proposal.html.twig', [
            'proposal' => $proposal,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::LOCKED, $proposal);

        $title = $proposal->getTitle();

        $em = $this->getDoctrine()->getManager();
        $em->remove($proposal);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The proposal '.$title.' has been removed');

        return $this->redirect($this->generateUrl('roots'));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function lockAction(Request $request, $slug)
    {
        $proposal = $this->getProposalBySlug($slug);

        $this->denyAccessUnlessGranted(ProposalVoter::CAN_BE_LOCKED, $proposal);

        $form = $this->createForm(LockProposalType::class, $proposal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proposal->lock();

            $em = $this->getDoctrine()->getManager();
            $em->persist($proposal);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show_motivation', [
                'slug' => $slug,
            ]));
        }

        return $this->render('App:Proposal:lock_proposal.html.twig', [
            'proposal' => $proposal,
            'form'     => $form->createView(),
        ]);
    }

    private function getProposalBySlug($slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        return $proposal;
    }
}
