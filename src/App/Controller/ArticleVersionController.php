<?php

namespace App\Controller;

use App\Entity\ArticleVersion;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleVersionController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function showAction($slug)
    {
        $articleVersion = $this->doctrine->getRepository(ArticleVersion::class)->findOneBySlug($slug);

        if (null === $articleVersion) {
            throw $this->createNotFoundException();
        }

        return $this->render('@App/ArticleVersion/show_article_version.html.twig', [
            'articleVersion' => $articleVersion,
        ]);
    }

    public function previousVersionAction($slug)
    {
        $repository     = $this->doctrine->getRepository(ArticleVersion::class);
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
        $repository     = $this->doctrine->getRepository(ArticleVersion::class);
        $articleVersion = $repository->findOneBySlug($slug);

        if (null === $articleVersion) {
            throw $this->createNotFoundException();
        }

        if ($articleVersion->isLastVersion()) {
            throw $this->createNotFoundException();
        }

        $nextVersion = $repository->findNextVersion($articleVersion);

        return $this->redirect($this->generateUrl('article_version_show', [
            'slug' => $nextVersion->getSlug(),
        ]));
    }
}
