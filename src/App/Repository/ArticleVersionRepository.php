<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\ArticleVersion;

class ArticleVersionRepository extends EntityRepository
{
    public function findPreviousVersion(ArticleVersion $articleVersion)
    {
        return $this->findOneBy([
            'recordedArticle' => $articleVersion->getRecordedArticle(),
            'versionNumber' => $articleVersion->getVersionNumber() - 1,
        ]);
    }

    public function findNextVersion(ArticleVersion $articleVersion)
    {
        return $this->findOneBy([
            'recordedArticle' => $articleVersion->getRecordedArticle(),
            'versionNumber' => $articleVersion->getVersionNumber() + 1,
        ]);
    }
}
