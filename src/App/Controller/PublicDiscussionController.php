<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\PublicDiscussion;
use App\Entity\Post;
use App\Form\Post\EditPostType;
use App\Form\Discussion\EditDiscussionType;
use App\Form\Discussion\SelectThemeType;
use App\Form\Discussion\SelectProposalType;

class PublicDiscussionController extends Controller
{
    public function showAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            return $this->render('App:Discussion:show_public_discussion.html.twig', [
                'discussion' => $discussion,
            ]);
        }

        if ($this->get('security.context')->isGranted('follow', $discussion)) {
            $discussion->removeUnreader($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();
        }

        if ($discussion->isLocked()) {
            return $this->render('App:Discussion:show_public_discussion.html.twig', [
                'discussion' => $discussion,
            ]);
        }

        $post = new Post();
        $form = $this->createForm(new EditPostType(), $post);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $post->setAuthor($user)->setDiscussion($discussion);
            $discussion->resetUnreaders();
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:show_public_discussion.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addToGlobalDiscussionsAction(Request $request)
    {
        $discussion = new PublicDiscussion();
        $form       = $this->createForm(new EditDiscussionType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $discussion->setType(PublicDiscussion::GLOBAL_DISCUSSION)
                ->addFollower($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:add_global_discussion.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addToThemeAction(Request $request, $slug)
    {
        $theme = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);

        if (null === $theme) {
            throw $this->createNotFoundException();
        }

        $discussion = new PublicDiscussion();
        $form       = $this->createForm(new EditDiscussionType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $discussion->setType(PublicDiscussion::THEME_DISCUSSION)
                ->setTheme($theme)
                ->addFollower($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:add_theme_discussion.html.twig', [
            'theme' => $theme,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addToProposalAction(Request $request, $slug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($slug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $discussion = new PublicDiscussion();
        $form       = $this->createForm(new EditDiscussionType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $discussion->setType(PublicDiscussion::PROPOSAL_DISCUSSION)
                ->setProposal($proposal)
                ->addFollower($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:add_proposal_discussion.html.twig', [
            'proposal' => $proposal,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new EditDiscussionType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:edit_discussion.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        if ($discussion->isGlobalDiscussion()) {
            $em->remove($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('global_room'));
        }

        if ($discussion->isThemeDiscussion()) {
            $themeSlug = $discussion->getTheme()->getSlug();
            $em->remove($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $themeSlug,
            ]));
        }

        if ($discussion->isProposalDiscussion()) {
            $proposalSlug = $discussion->getProposal()->getSlug();
            $em->remove($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('proposal_show', [
                'slug' => $proposalSlug,
            ]));
        }
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveToGlobalAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->setType(PublicDiscussion::GLOBAL_DISCUSSION)
            ->setProposal(null)
            ->setTheme(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
        ]));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveToThemeAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->setType(PublicDiscussion::THEME_DISCUSSION)
            ->setProposal(null)
            ->setTheme(null);
        $form = $this->createForm(new SelectThemeType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveToProposalAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->setType(PublicDiscussion::PROPOSAL_DISCUSSION)
            ->setProposal(null)
            ->setTheme(null);
        $form = $this->createForm(new SelectProposalType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function lockAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->setLocked(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function unlockAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->setLocked(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function followAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->addFollower($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function stopFollowingAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $discussion->removeFollower($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }
}
