<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Post;
use App\Entity\PrivateDiscussion;
use App\Form\Post\EditPostType;
use App\Form\Discussion\AddPrivateDiscussionType;
use App\Form\Discussion\EditDiscussionType;
use App\Form\SelectUserType;
use App\Security\Authorization\Voter\PrivateDiscussionVoter;

class PrivateDiscussionController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('view', $discussion);

        $discussion->removeUnreader($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        if ($discussion->isLocked()) {
            return $this->render('App:Discussion:show_private_discussion.html.twig', [
                'discussion' => $discussion,
            ]);
        }

        $post = new Post();
        $form = $this->createForm(EditPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser())->setDiscussion($discussion);
            $discussion->resetUnreaders();
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:show_private_discussion.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(PrivateDiscussionVoter::CAN_BE_EDITED, $discussion);

        $form = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:edit_private_discussion.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function lockAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(PrivateDiscussionVoter::CAN_BE_LOCKED, $discussion);

        $discussion->setLocked(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('private_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }
}
