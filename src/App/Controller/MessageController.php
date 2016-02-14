<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Message;
use App\Form\SendMessageType;

class MessageController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showAction($slug)
    {
        $message = $this->getDoctrine()->getRepository('App:Message')->findOneBySlug($slug);

        if (null === $message) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('view', $message)) {
            throw new AccessDeniedException();
        }

        if ($this->get('security.context')->isGranted('first_reading', $message)) {
            $message->addReader($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
        }

        return $this->render('App:Message:show.html.twig', [
            'message' => $message,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function sendAction(Request $request)
    {
        $message = new Message();
        $form    = $this->createForm(new SendMessageType(), $message);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $message->setSender($this->getUser());
            $em->persist($message);
            $em->flush();

            return $this->redirect($this->generateUrl('message_show', [
                'slug' => $message->getSlug(),
            ]));
        }

        return $this->render('App:Message:send.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function replyAction(Request $request, $slug)
    {
        $message = $this->getDoctrine()->getRepository('App:Message')->findOneBySlug($slug);

        if (null === $message) {
            throw $this->createNotFoundException();
        }

        if (false === $this->get('security.context')->isGranted('reply', $message)) {
            throw new AccessDeniedException();
        }

        $reply = new Message();
        $reply->editReply($message);
        $form = $this->createForm(new SendMessageType(), $reply);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $reply->setSender($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($reply);
            $em->flush();

            return $this->redirect($this->generateUrl('message_show', [
                'slug' => $reply->getSlug(),
            ]));
        }

        return $this->render('App:Message:send.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
