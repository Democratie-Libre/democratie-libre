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
use App\Form\Discussion\ChangeAdminType;

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

        if (false === $this->get('security.context')->isGranted('view', $discussion)) {
            throw new AccessDeniedException();
        }

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
        $form = $this->createForm(new EditPostType(), $post);
        $form->handleRequest($request);

        if ($form->isValid()) {
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
    public function addAction(Request $request)
    {
        $discussion = new PrivateDiscussion();
        $user       = $this->getUser();
        $form       = $this->createForm(new AddPrivateDiscussionType($user->getId()), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $discussion->setAdmin($user)->resetUnreaders();
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
            ]));
        }

        return $this->render('App:Discussion:add_private_discussion.html.twig', [
            'form' => $form->createView(),
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

        if (false === $this->get('security.context')->isGranted('edit', $discussion)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new EditDiscussionType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:edit_discussion.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addMemberAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('add_member', $discussion)) {
            throw new AccessDeniedException();
        }

        $members    = $discussion->getMembers();
        $membersIds = [];

        foreach ($members as $member) {
            $membersIds[] = $member->getId();
        }

        $form = $this->createForm(new SelectUserType($membersIds));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $member = $form->get('user')->getData();
            $discussion->addMember($member);
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:add_member.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeMemberAction($discussionSlug, $memberId)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($discussionSlug);
        $member     = $this->getDoctrine()->getRepository('App:User')->find($memberId);

        if ((null ===  $discussion) or (null === $member)) {
            throw $this->createNotFoundException();
        }

        if (false === $discussion->hasMember($member)) {
            throw new \Exception(
                'User not among the members.'
            );
        }

        $user = $this->getUser();

        if (($user !== $discussion->getAdmin()) and ($user !== $member)) {
            throw new AccessDeniedException();
        }

        if ($member === $discussion->getAdmin()) {
            throw new AccessDeniedException();
        }

        $discussion->removeMember($member);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        if ($user === $member) {
            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->redirect($this->generateUrl('private_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function changeAdminAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null ===  $discussion) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('edit', $discussion)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ChangeAdminType(), $discussion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discussion);
            $em->flush();

            return $this->redirect($this->generateUrl('private_discussion_show', [
                'slug' => $discussion->getSlug(),
             ]));
        }

        return $this->render('App:Discussion:change_admin_private_discussion.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Request $request, $slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('edit', $discussion)) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('profile'));
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

        if (false === $this->get('security.context')->isGranted('edit', $discussion)) {
            throw new AccessDeniedException();
        }

        $discussion->setLocked(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('private_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function unlockAction($slug)
    {
        $discussion = $this->getDoctrine()->getRepository('App:PrivateDiscussion')->findOneBySlug($slug);

        if (null === $discussion) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('edit', $discussion)) {
            throw new AccessDeniedException();
        }

        $discussion->setLocked(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($discussion);
        $em->flush();

        return $this->redirect($this->generateUrl('private_discussion_show', [
            'slug' => $discussion->getSlug(),
         ]));
    }
}
