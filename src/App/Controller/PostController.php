<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\PublicDiscussion;
use App\Entity\PrivateDiscussion;
use App\Form\Post\MovePostType;

class PostController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[IsGranted('ROLE_USER')]
    public function openPrivateDiscussionAction($id)
    {
        $post = $this->doctrine->getRepository(Post::class)->find($id);

        if (null ===  $post) {
            throw $this->createNotFoundException();
        }

        if ($post->getDiscussion() instanceof PrivateDiscussion) {
            throw new \Exception(
                'A private discussion cannot be opened from a private discussion post.'
            );
        }

        $user         = $this->getUser();
        $authorOfPost = $post->getAuthor();

        if ($authorOfPost === $user) {
            throw new \Exception(
                'The user cannot open a discussion with himself.'
            );
        }

        $discussion = PrivateDiscussion::create();
        $discussion
            ->setTitle($post->getDiscussion()->getTitle())
            ->setAdmin($user)
            ->addMember($authorOfPost)
            ->resetUnreaders($authorOfPost);
        $em = $this->doctrine->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('private_discussion_show', [
            'slug' => $discussion->getSlug(),
        ]));
    }

    #[IsGranted('ROLE_ADMIN')]
    public function moveAction(Request $request, $id)
    {
        $post = $this->doctrine->getRepository(Post::class)->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        $discussion = $post->getDiscussion();

        if (!$discussion instanceof PublicDiscussion) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(MovePostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDiscussion = $form->get('discussion')->getData();
            $em = $this->doctrine->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $newDiscussion->getSlug(),
            ]));
        }

        return $this->render('@App/Post/move_post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function deleteAction($id)
    {
        $post = $this->doctrine->getRepository(Post::class)->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        $discussion = $post->getDiscussion();

        if (!$discussion instanceof PublicDiscussion) {
            throw new AccessDeniedException();
        }

        $em = $this->doctrine->getManager();
        $em->remove($post);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
        ]));
    }
}
