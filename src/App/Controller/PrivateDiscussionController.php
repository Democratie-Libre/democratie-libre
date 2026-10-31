<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Post;
use App\Entity\PrivateDiscussion;
use App\Form\Post\EditPostType;
use App\Form\Discussion\AddPrivateDiscussionType;
use App\Form\Discussion\EditDiscussionType;
use App\Form\SelectUserType;
use App\Security\Authorization\Voter\PrivateDiscussionVoter;

class PrivateDiscussionController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function showAction(Request $request, $slug)
    {
        $discussion = $this->doctrine->getRepository(PrivateDiscussion::class)->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('view', $discussion);

        $discussion->removeUnreader($this->getUser());
        $em = $this->doctrine->getManager();
        $em->persist($discussion);
        $em->flush();

        if ($discussion->isLocked()) {
            return $this->render('@App/Discussion/show_private_discussion.html.twig', [
                'discussion' => $discussion,
            ]);
        }

        $post = new Post();
        $form = $this->createForm(EditPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser())->setDiscussion($discussion);
            $discussion->resetUnreaders();
            $em = $this->doctrine->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('@App/Discussion/show_private_discussion.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function editAction(Request $request, $slug)
    {
        $discussion = $this->doctrine->getRepository(PrivateDiscussion::class)->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(PrivateDiscussionVoter::CAN_BE_EDITED, $discussion);

        $form = $this->createForm(EditDiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('@App/Discussion/edit_private_discussion.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function lockAction($slug)
    {
        $discussion = $this->doctrine->getRepository(PrivateDiscussion::class)->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(PrivateDiscussionVoter::CAN_BE_LOCKED, $discussion);

        $discussion->setLocked(true);
        $em = $this->doctrine->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('private_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }
}
