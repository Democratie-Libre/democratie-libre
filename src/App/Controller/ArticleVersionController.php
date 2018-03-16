<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ArticleVersionController extends Controller
{
    public function showAction($slug)
    {
        $articleVersion = $this->getDoctrine()->getRepository('App:ArticleVersion')->findOneBySlug($slug);

        if (null === $articleVersion) {
            throw $this->createNotFoundException();
        }

        return $this->render('App:ArticleVersion:show_article_version.html.twig', [
            'articleVersion' => $articleVersion,
        ]);
    }

    public function previousVersionAction($slug)
    {
        $repository  = $this->getDoctrine()->getRepository('App:ArticleVersion');
        $articleVersion = $repository->findOneBySlug($slug);

        if (null === $articleVersion) {
            throw $this->createNotFoundException();
        }

        if ($articleVersion->isFirstVersion()) {
            throw $this->createNotFoundException();
        }

        $previousVersion = $repository->findPreviousVersion($articleVersion);

        return $this->redirect($this->generateUrl('article_version_show', [
            'slug' => $previousVersion->getSlug(),
        ]));
    }

    public function nextVersionAction($slug)
    {
        $repository  = $this->getDoctrine()->getRepository('App:ArticleVersion');
        $articleVersion = $repository->findOneBySlug($slug);

        if (null === $articleVersion) {
            throw $this->createNotFoundException();
        }

        if ($articleVersion->isLastVersion()) {
            return $this->redirect($this->generateUrl('article_show', [
                'slug' => $articleVersion->getRecordedArticle()->getSlug(),
            ]));
        }

        $nextVersion = $repository->findNextVersion($articleVersion);

        return $this->redirect($this->generateUrl('article_version_show', [
            'slug' => $nextVersion->getSlug(),
        ]));
    }
}
