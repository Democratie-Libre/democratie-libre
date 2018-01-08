<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\User;
use App\Form\Security\RegisterType;
use App\Form\Security\EditEmailType;
use App\Form\Security\EditPasswordType;
use App\Form\Security\EditAvatarType;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        // An authenticated user shouldn’t see this page
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('index'));
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $loginError = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $loginError = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($loginError) {
            $this->addFlash('danger', $this->get('translator')->trans($loginError->getMessage()));
        }

        return $this->render('App:Security:login.html.twig', [
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
        ]);
    }

    public function registerAction(Request $request)
    {
        $session = $request->getSession();

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('index'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RegisterType());
        $form
            ->add('save', 'submit', [
                'label' => 'Inscription',
                'attr'  => ['class' => 'btn btn-primary btn-lg'],
            ])
        ;

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', sprintf('Bienvenue %s !', $user->getUsername()));

            // Automatically logging in after registration
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirect($this->generateUrl('index'));
        }

        return $this->render('App:Security:register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function profileAction()
    {
        $user = $this->getUser();

        return $this->render('App:Security:profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function emailEditAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(new EditEmailType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre email a été modifié !');

            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->render('App:Security:edit_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function passwordEditAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(new EditPasswordType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setPlainPassword($form->get('plainPassword')->get('first')->getData());
            $user->setPassword(null);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour !');

            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->render('App:Security:edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function avatarEditAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(new EditAvatarType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->render('App:Security:edit_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
