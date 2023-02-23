<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use App\Form\Security\RegisterType;
use App\Form\Security\EditEmailType;
use App\Form\Security\EditPasswordType;
use App\Form\Security\EditAvatarType;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render('App:Security:login.html.twig', [
            'last_username' => $helper->getLastUsername(),
            'error'         => $helper->getLastAuthenticationError(),
        ]);
    }

    public function registerAction(Request $request)
    {
        $session = $request->getSession();

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('index'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(RegisterType::class);
        $form
            ->add('save', SubmitType::class, [
                'label' => 'Inscription',
                'attr'  => ['class' => 'btn btn-primary btn-lg'],
            ])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function showPrivateDiscussionsAction(Request $request)
    {
        return $this->render('App:Security:show_profile_private_discussions.html.twig', [
            'locked_discussions' => False,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showLockedPrivateDiscussionsAction(Request $request)
    {
        return $this->render('App:Security:show_profile_private_discussions.html.twig', [
            'locked_discussions' => True,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showFollowedDiscussionsAction(Request $request)
    {
        return $this->render('App:Security:show_profile_followed_discussions.html.twig');
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showProposalsAction(Request $request)
    {
        return $this->render('App:Security:show_profile_proposals.html.twig');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAdministrationAction()
    {
        $user = $this->getUser();

        $numberOfUsers     = $this->getDoctrine()->getRepository('App:User')->count([]);
        $users             = $this->getDoctrine()->getRepository(User::class)->findAll();
        $numberOfThemes    = $this->getDoctrine()->getRepository('App:Theme')->count([]);
        $numberOfProposals = $this->getDoctrine()->getRepository('App:Proposal')->count([]);

        return $this->render('App:Security:show_profile_administration.html.twig', [
            'user'                => $user,
            'number_of_users'     => $numberOfUsers,
            'users'               => $users,
            'number_of_themes'    => $numberOfThemes,
            'number_of_proposals' => $numberOfProposals,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function emailEditAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(EditEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        $form = $this->createForm(EditPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        $form = $this->createForm(EditAvatarType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->render('App:Security:edit_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
