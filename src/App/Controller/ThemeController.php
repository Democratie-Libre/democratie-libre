<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Theme;
use App\Form\ThemeType;
use App\Form\EditThemeType;
use Symfony\Component\Security\Core\User\UserInterface;

class ThemeController extends Controller
{
    public function showAction($slug)
    {
        $theme = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);
        $user  = $this->getUser();

        if (null ===  $theme) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Theme:show.html.twig', [
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
        $form  = $this->createForm(new ThemeType(), $theme);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $theme->setLvl(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request, $slug)
    {
        $theme = new Theme();
        $form  = $this->createForm(new ThemeType(), $theme);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $parent = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);

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

        return $this->render('App:Theme:add.html.twig', [
            'form' => $form->createView(),
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
            $this->get('session')->getFlashBag()->add('info', 'A theme cannot be deleted if it contains any proposal');

            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $theme->getSlug(),
            ]));
        }

        $parent = $theme->getParent();
        $repository->removeFromTree($theme);
        $this->getDoctrine()->getManager()->clear(); // clear cached nodes
        // it will remove this node from tree and reparent all children
        $this->get('session')->getFlashBag()->add('info', 'The theme entitled '.$theme->getTitle().' has been deleted');

        if ($parent instanceof Theme) {
            return $this->redirect($this->generateUrl('theme_show', [
                'slug' => $parent->getSlug(),
            ]));
        }

        return $this->redirect($this->generateUrl('index'));
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

        // in this array are the id of the theme considered, and the ids of all its descendants
        $descendantsId = $repository->getChildrenId($theme, false, null, 'ASC', true);
        $form          = $this->createForm(new EditThemeType($descendantsId), $theme);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $theme->setParent($form->get('parent')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show', [
                    'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function showDiscussionsAction($slug)
    {
        $theme = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);

        if (null === $theme) {
            throw $this->createNotFoundException();
        }

        $discussions = $this->getDoctrine()->getRepository('App:Discussion')->findByTheme($theme);

        return $this->render('App:Theme:discussions.html.twig', [
            'discussions' => $discussions,
        ]);
    }
}
