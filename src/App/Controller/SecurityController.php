<?php

namespace App\Controller;

use App\Entity\Proposal;
use App\Entity\Theme;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use App\Form\Security\RegisterType;
use App\Form\Security\EditEmailType;
use App\Form\Security\EditPasswordType;
use App\Form\Security\EditAvatarType;

class SecurityController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/login', name: 'login')]
    public function loginAction(Request $request, AuthenticationUtils $helper)
    {
        return $this->render('@App/Security/login.html.twig', [
            'last_username' => $helper->getLastUsername(),
            'error'         => $helper->getLastAuthenticationError(),
        ]);
    }

    public function registerAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $session = $request->getSession();

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('index'));
        }

        $em = $this->doctrine->getManager();

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
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirect($this->generateUrl('index'));
        }

        return $this->render('@App/Security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function showPrivateDiscussionsAction(Request $request)
    {
        return $this->render('@App/Security/show_profile_private_discussions.html.twig', [
            'locked_discussions' => False,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function showLockedPrivateDiscussionsAction(Request $request)
    {
        return $this->render('@App/Security/show_profile_private_discussions.html.twig', [
            'locked_discussions' => True,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function showFollowedDiscussionsAction(Request $request)
    {
        return $this->render('@App/Security/show_profile_followed_discussions.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    public function showProposalsAction(Request $request)
    {
        return $this->render('@App/Security/show_profile_proposals.html.twig');
    }

    #[IsGranted('ROLE_ADMIN')]
    public function showAdministrationAction()
    {
        $user = $this->getUser();

        $numberOfUsers     = $this->doctrine->getRepository(User::class)->count([]);
        $users             = $this->doctrine->getRepository(User::class)->findAll();
        $numberOfThemes    = $this->doctrine->getRepository(Theme::class)->count([]);
        $numberOfProposals = $this->doctrine->getRepository(Proposal::class)->count([]);

        return $this->render('@App/Security/show_profile_administration.html.twig', [
            'user'                => $user,
            'number_of_users'     => $numberOfUsers,
            'users'               => $users,
            'number_of_themes'    => $numberOfThemes,
            'number_of_proposals' => $numberOfProposals,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function emailEditAction(Request $request)
    {
        $em   = $this->doctrine->getManager();
        $user = $this->getUser();

        $form = $this->createForm(EditEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre email a été modifié !');

            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->render('@App/Security/edit_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function passwordEditAction(Request $request)
    {
        $em   = $this->doctrine->getManager();
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

        return $this->render('@App/Security/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function avatarEditAction(Request $request)
    {
        $em   = $this->doctrine->getManager();
        $user = $this->getUser();

        $form = $this->createForm(EditAvatarType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('profile'));
        }

        return $this->render('@App/Security/edit_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
