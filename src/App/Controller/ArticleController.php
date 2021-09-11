<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Article;
use App\Entity\ArticleVersion;
use App\Form\Article\EditArticleType;
use App\Form\Article\RemoveArticleType;
use App\Security\Authorization\Voter\ArticleVoter;
use App\Security\Authorization\Voter\ProposalVoter;

class ArticleController extends Controller
{
    public function showContentAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Article:show_article_content.html.twig', [
            'article' => $article,
        ]);
    }

    public function showDiscussionsAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Article:show_article_discussions.html.twig', [
            'article'            => $article,
            'locked_discussions' => false,
        ]);
    }

    public function showLockedDiscussionsAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Article:show_article_discussions.html.twig', [
            'article'            => $article,
            'locked_discussions' => true,
        ]);
    }

    public function showVersioningAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Article:show_article_versioning.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showAdministrationAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        $proposal = $article->getProposal();
        $this->denyAccessUnlessGranted(ProposalVoter::CAN_BE_EDITED, $proposal);

        return $this->render('App:Article:show_article_administration.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request, $proposalSlug)
    {
        $proposal = $this->getDoctrine()->getRepository('App:Proposal')->findOneBySlug($proposalSlug);

        if (null === $proposal) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(ProposalVoter::CAN_BE_EDITED, $proposal);

        $article = new Article();
        $form    = $this->createForm(EditArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setProposal($proposal);

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirect($this->generateUrl('article_show', [
                'slug' => $article->getSlug(),
            ]));
        }

        return $this->render('App:Article:add_article.html.twig', [
            'form'          => $form->createView(),
            'article'       => $article,
            'proposal'      => $proposal,
            'articleNumber' => $proposal->getNumberOfPublishedArticles() + 1,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Request $request, $slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null ===  $article) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(ArticleVoter::CAN_BE_EDITED, $article);

        $form = $this->createForm(EditArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article
                ->incrementVersionNumber()
                ->snapshot()
            ;

            $proposal = $article->getProposal();

            $proposal
                ->incrementVersionNumber()
                ->snapshot()
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirect($this->generateUrl('article_show', [
                'slug' => $article->getSlug(),
            ]));
        }

        return $this->render('App:Article:edit_article.html.twig', [
            'form'     => $form->createView(),
            'article'  => $article,
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function removeAction(Request $request, $slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(ArticleVoter::CAN_BE_REMOVED, $article);

        $form = $this->createForm(RemoveArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->remove();

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'The article '.$article->getTitle().' has been removed from the proposal.');

            return $this->redirect($this->generateUrl('article_show', [
                'slug' => $slug,
            ]));
        }

        return $this->render('App:Article:remove_article.html.twig', [
            'article' => $article,
            'form'    => $form->createView(),
        ]);
    }
}
