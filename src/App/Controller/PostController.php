<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\PublicDiscussion;
use App\Form\Post\MovePostType;

class PostController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveAction(Request $request, $id)
    {
        $post = $this->getDoctrine()->getRepository('App:Post')->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        $discussion = $post->getDiscussion();

        if (!$discussion instanceof PublicDiscussion) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(MovePostType::class, $post);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $newDiscussion = $form->get('discussion')->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('public_discussion_show', [
                'slug' => $newDiscussion->getSlug(),
            ]));
        }

        return $this->render('App:Post:move_post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $post = $this->getDoctrine()->getRepository('App:Post')->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        $discussion = $post->getDiscussion();

        if (!$discussion instanceof PublicDiscussion) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->redirect($this->generateUrl('public_discussion_show', [
            'slug' => $discussion->getSlug(),
        ]));
    }
}
