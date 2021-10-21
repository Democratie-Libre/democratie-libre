<?php

namespace App\Controller;

use App\Security\Authorization\Voter\ProposalVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Theme;
use App\Entity\Proposal;
use App\Form\Theme\EditThemeType;
use App\Form\Theme\MoveThemeType;

class ThemeController extends Controller
{
    public function showProposalsAction($slug)
    {
        $theme = $this->getThemeBySlug($slug);

        return $this->render('App:Theme:show_theme_proposals.html.twig', [
            'theme'            => $theme,
            'locked_proposals' => false,
        ]);
    }

    public function showLockedProposalsAction($slug)
    {
        $theme = $this->getThemeBySlug($slug);

        return $this->render('App:Theme:show_theme_proposals.html.twig', [
            'theme'            => $theme,
            'locked_proposals' => True,
        ]);
    }

    public function showDiscussionsAction($slug)
    {
        $theme = $this->getThemeBySlug($slug);

        return $this->render('App:Theme:show_theme_discussions.html.twig', [
            'theme'              => $theme,
            'locked_discussions' => False,
        ]);
    }

    public function showLockedDiscussionsAction($slug)
    {
        $theme = $this->getThemeBySlug($slug);

        return $this->render('App:Theme:show_theme_discussions.html.twig', [
            'theme'              => $theme,
            'locked_discussions' => True,
        ]);
    }

    public function showChildrenAction($slug)
    {
        $theme = $this->getThemeBySlug($slug);

        return $this->render('App:Theme:show_theme_children.html.twig', [
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

            return $this->redirect($this->generateUrl('theme_show_proposals', [
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

            return $this->redirect($this->generateUrl('theme_show_proposals', [
                'slug' => $theme->getSlug(),
            ]));
        }

        return $this->render('App:Theme:add_theme.html.twig', [
            'form'  => $form->createView(),
            'theme' => $parent,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $slug)
    {
        $theme      = $this->getThemeBySlug($slug);
        $repository = $this->getDoctrine()->getRepository('App:Theme');

        $form = $this->createForm(EditThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show_proposals', [
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
        $theme      = $this->getThemeBySlug($slug);
        $repository = $this->getDoctrine()->getRepository('App:Theme');

        // in this array are the id of the theme considered, and the ids of all its descendants
        $descendantsIds = $repository->getChildrenId($theme, false, null, 'ASC', true);
        $form = $this->createForm(MoveThemeType::class, $theme, [
            'descendantsIds' => $descendantsIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parent = $form->get('parent')->getData();

            // if the user enters a null value for the parent, the theme becomes a root
            if ($parent === null) {
                $theme->setParent();
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();

            return $this->redirect($this->generateUrl('theme_show_proposals', [
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
        $theme      = $this->getThemeBySlug($slug);
        $repository = $this->getDoctrine()->getRepository('App:Theme');

        if (false === $theme->isEmpty()) {
            $this->addFlash('info', 'Une thématique ne peut pas être supprimée si elle contient des propositions !');

            return $this->redirect($this->generateUrl('theme_show_proposals', [
                'slug' => $theme->getSlug(),
            ]));
        }

        $em = $this->getDoctrine()->getManager();

        // we suppress all the discussions associated before removing the theme
        // we do it manually because Doctrine cascading does not work has we expect here
        $discussions = $theme->getDiscussions();

        foreach ($discussions as $discussion) {
            $em->remove($discussion);
        }

        $em->flush();

        $parent = $theme->getParent();
        $repository->removeFromTree($theme);
        $em->clear(); // clear cached nodes
        // it will remove this node from tree and reparent all children

        $this->addFlash('info', 'La thématique '.$theme->getTitle().' a été supprimée !');

        if ($parent instanceof Theme) {
            return $this->redirect($this->generateUrl('theme_show_proposals', [
                'slug' => $parent->getSlug(),
            ]));
        }

        return $this->redirect($this->generateUrl('roots'));
    }

    private function getThemeBySlug($slug)
    {
        $theme = $this->getDoctrine()->getRepository('App:Theme')->findOneBySlug($slug);

        if (null === $theme) {
            throw $this->createNotFoundException();
        }

        return $theme;
    }
}
