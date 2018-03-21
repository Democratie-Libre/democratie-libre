<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This entity allows to record the versions of the articles.
 * It is instanciated each time an article is modified.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ArticleVersionRepository")
 * @UniqueEntity(fields={"slug","title"})
 * @ORM\HasLifecycleCallbacks
 */
class ArticleVersion 
{
    /**
     * @ORM\Column(type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Length(
     *      min = "2",
     *      max = "255"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $snapDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motivation;

    /**
     * @ORM\Column(type="integer")
     */
    private $versionNumber;

    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="articles", cascade={"persist"})
     * @Assert\Valid()
     */
    private $proposal;

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="versioning")
     * @Assert\Valid()
     */
    private $recordedArticle;

    public function __construct(Article $article)
    {
        $this->title                 = sprintf('%s_v%s', $article->getTitle(), $article->getVersionNumber());
        $this->snapDate              = new \Datetime(); 
        $this->content               = $article->getContent();
        $this->motivation            = $article->getMotivation();
        $this->versionNumber         = $article->getVersionNumber();
        $this->proposal              = $article->getProposal();
        $this->recordedArticle       = $article;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSnapDate()
    {
        return $this->snapDate;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getMotivation()
    {
        return $this->motivation;
    }

    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    public function getRecordedArticle()
    {
        return $this->recordedArticle;
    }

    public function isFirstVersion()
    {
        return $this->getVersionNumber() === 1;
    }

    public function isLastVersion()
    {
        $thisVersionNumber = $this->getVersionNumber();
        $recordedArticleVersionNumber = $this->getRecordedArticle()
            ->getVersionNumber();

        return $thisVersionNumber === $recordedArticleVersionNumber;
    }
}
