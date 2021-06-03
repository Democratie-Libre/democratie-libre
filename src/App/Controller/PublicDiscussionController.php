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
use App\Form\SelectThemeType;
use App\Form\SelectProposalType;
use App\Form\SelectArticleType;

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

        if ($this->isGranted('follow', $discussion)) {
            $discussion->removeUnreader($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();
        }

        if ($discussion->isLocked()) {
            return $this->render('App:Discussion:show_locked_public_discussion.html.twig', [
                'discussion' => $discussion,
            ]);
        }

        $post = new Post();
        $form = $this->createForm(EditPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        $discussion = PublicDiscussion::createGlobalDiscussion();
        $form       = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $discussion->addFollower($this->getUser());

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

        $discussion = PublicDiscussion::createThemeDiscussion($theme);
        $form       = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $discussion->addFollower($this->getUser());

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

        $this->denyAccessUnlessGranted('published', $proposal);

        $discussion = PublicDiscussion::createProposalDiscussion($proposal);
        $form       = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $discussion->addFollower($this->getUser());

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
     * @Security("has_role('ROLE_USER')")
     */
    public function addToArticleAction(Request $request, $slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        $proposal = $article->getProposal();
        $this->denyAccessUnlessGranted('published', $proposal);

        $discussion = PublicDiscussion::createArticleDiscussion($article);
        $form       = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $discussion->addFollower($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:add_article_discussion.html.twig', [
            'article' => $article,
            'form'    => $form->createView(),
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

        $this->denyAccessUnlessGranted('published', $discussion);

        $form = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:edit_public_discussion.html.twig', [
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

        $this->denyAccessUnlessGranted('published', $discussion);

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

            return $this->redirect($this->generateUrl('theme_show_proposals', [
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

        if ($discussion->isArticleDiscussion()) {
            $articleSlug = $discussion->getArticle()->getSlug();
            $em->remove($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('article_show', [
                'slug' => $articleSlug,
            ]));
        }

        throw new \LogicException('This code should not be reached!');
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

        $discussion->moveAs(PublicDiscussion::GLOBAL_DISCUSSION);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The discussion has been moved in the global room');

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

        $form = $this->createForm(SelectThemeType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hostTheme = $form->get('theme')->getData();
            $discussion->moveAs(PublicDiscussion::THEME_DISCUSSION, $hostTheme);
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'The discussion has been moved in the theme '.$hostTheme->getTitle());

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:move_public_discussion_to_theme.html.twig', [
            'form'       => $form->createView(),
            'discussion' => $discussion,
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

        $form = $this->createForm(SelectProposalType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hostProposal = $form->get('proposal')->getData();
            $this->denyAccessUnlessGranted('published', $hostProposal);
            $discussion->moveAs(PublicDiscussion::PROPOSAL_DISCUSSION, $hostProposal);
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'The discussion has been moved in the proposal '.$hostProposal->getTitle());

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:move_public_discussion_to_proposal.html.twig', [
            'form'       => $form->createView(),
            'discussion' => $discussion,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveToArticleAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PublicDiscussion')->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(SelectArticleType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hostArticle = $form->get('article')->getData();
            $this->denyAccessUnlessGranted('published', $hostArticle);
            $discussion->moveAs(PublicDiscussion::ARTICLE_DISCUSSION, $hostArticle);
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'The discussion has been moved in the article '.$hostArticle->getTitle());

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:move_public_discussion_to_article.html.twig', [
            'form'       => $form->createView(),
            'discussion' => $discussion,
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

        $this->denyAccessUnlessGranted('published', $discussion);

        $discussion->setLocked(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The discussion has been locked');

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

        $this->denyAccessUnlessGranted('locked', $discussion);

        $discussion->setLocked(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The discussion has been unlocked');

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

        $this->get('session')->getFlashBag()->add('info', 'You follow the discussion');

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

        $this->get('session')->getFlashBag()->add('info', 'You do not follow the discussion anymore');

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }
}
