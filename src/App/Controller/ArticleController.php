<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Article;
use App\Entity\ArticleVersion;
use App\Form\Article\EditArticleType;

class ArticleController extends Controller
{
    public function showAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:Article:show_article.html.twig', [
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

        $this->denyAccessUnlessGranted('edit', $proposal);

        $article          = new Article();
        $numberOfArticles = $proposal->getNumberOfArticles();
        $form             = $this->createForm(EditArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article
                ->setProposal($proposal)
                ->setNumber($numberOfArticles + 1)
                ->snapshot()
            ;

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

        return $this->render('App:Article:add_article.html.twig', [
            'form'             => $form->createView(),
            'article'          => $article,
            'proposal'         => $proposal,
            'articleNumber'    => $numberOfArticles + 1,
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

        $this->denyAccessUnlessGranted('edit', $article);

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
    public function deleteAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('App:Article')->findOneBySlug($slug);

        if (null ===  $article) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('delete', $article);

        $proposal = $article->getProposal();
        $proposal
            ->removeArticle($article)
            ->incrementVersionNumber()
            ->snapshot()
        ;

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'The article '.$article->getTitle().' has been suppressed');

        return $this->redirect($this->generateUrl('proposal_show', [
            'slug' => $proposal->getSlug(),
        ]));
    }
}
