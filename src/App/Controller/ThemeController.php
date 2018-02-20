<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Theme;
use App\Form\Theme\EditThemeType;
use App\Form\Theme\MoveThemeType;

class ThemeController extends Controller
{
    public function showAction($slug)
    {
        $theme = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);
        $user  = $this->getUser();

        if (null ===  $theme) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Theme:show_theme.html.twig', [
            'theme' => $theme,
        ]);
    }

    /**
     * Creates a new Theme entity of level 0 (root).
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addRootAction(Request $request)
    {
        $theme = new Theme();
        $form  = $this->createForm(EditThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $theme->setLvl(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:add_root.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request, $slug)
    {
        $theme = new Theme();
        $form  = $this->createForm(EditThemeType::class, $theme);
        $form->handleRequest($request);
        $parent = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);

        if ($form->isSubmitted() && $form->isValid()) {

            if (null === $parent) {
                throw $this->createNotFoundException();
            }

            $theme->setParent($parent);
            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:add_theme.html.twig', [
            'form'   => $form->createView(),
            'parent' => $parent,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $slug)
    {
        $repository = $this->getDoctrine()->getRepository('App:Theme');
        $theme      = $repository->findOneBySlug($slug);

        if (null === $theme) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EditThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:edit_theme.html.twig', [
            'form'   => $form->createView(),
            'theme'  => $theme,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moveAction(Request $request, $slug)
    {
        $repository = $this->getDoctrine()->getRepository('App:Theme');
        $theme      = $repository->findOneBySlug($slug);

        if (null === $theme) {
            throw $this->createNotFoundException();
        }

        // in this array are the id of the theme considered, and the ids of all its descendants
        $descendantsIds = $repository->getChildrenId($theme, false, null, 'ASC', true);
        $form = $this->createForm(MoveThemeType::class, $theme, [
            'descendantsIds' => $descendantsIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:move_theme.html.twig', [
            'form'   => $form->createView(),
            'theme'  => $theme,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $slug)
    {
        $repository = $this->getDoctrine()->getRepository('App:Theme');
        $theme      = $repository->findOneBySlug($slug);

        if (null === $theme) {
            throw $this->createNotFoundException();
        }

        if (false === $theme->isEmpty()) {
            $this->addFlash('info', 'Une thématique ne peut pas être supprimée si elle contient des propositions !');

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        $parent = $theme->getParent();
        $repository->removeFromTree($theme);
        $this->getDoctrine()->getManager()->clear(); // clear cached nodes
        // it will remove this node from tree and reparent all children
        $this->addFlash('info', 'La thématique '.$theme->getTitle().' a été supprimée !');

        if ($parent instanceof Theme) {
            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $parent->getSlug(),
            ]));
        }

        return $this->redirect($this->generateUrl('roots'));
    }
}
