<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"slug"})
 * @ORM\HasLifecycleCallbacks
 */
class Article
{
    const PUBLISHED = 'published';
    const REMOVED   = 'removed';
    const LOCKED    = 'locked';

    /**
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="string", length=255, options={"default" : Article::PUBLISHED})
     */
    private $status = self::PUBLISHED;

    /**
     * If the article has been removed by the author of the proposal, it should
     * be justified here.
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(
     *      max = 400,
     * )
     */
    private $removingExplanation;

    /**
     * If the article is locked, its number will be zero.
     *
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Regex("/[a-zA-Z0-9]+/")
     * @Assert\Length(
     *      max = 100,
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $lastEditDate;

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
     */
    private $proposal;

    /**
     * @ORM\OneToMany(targetEntity="PublicDiscussion", mappedBy="article", cascade={"persist", "remove"})
     */
    private $discussions;

    /**
     * @ORM\OneToMany(targetEntity="ArticleVersion", mappedBy="recordedArticle", cascade={"persist", "remove"})
     * @ORM\OrderBy({"versionNumber" = "DESC"})
     */
    private $versioning;

    public function __construct()
    {
        $this->status               = self::PUBLISHED;
        $this->removingExplanation  = null;
        $this->creationDate         = new \DateTime();
        $this->versionNumber        = 1;
        $this->discussions          = new ArrayCollection();
        $this->versioning           = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function isPublished()
    {
        return $this->getStatus() === self::PUBLISHED;
    }

    public function setRemovingExplanation($removingExplanation)
    {
        $this->removingExplanation = $removingExplanation;

        return $this;
    }

    public function getRemovingExplanation()
    {
        return $this->removingExplanation;
    }

    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function decreaseNumber()
    {
        if ($this->number === 0) {
            throw new Exception('An article number cannot be under 0 !');
        }

        $this->number -= 1;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setLastEditDate()
    {
        $this->lastEditDate = new \DateTime();

        return $this;
    }

    public function getLastEditDate()
    {
        return $this->lastEditDate;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setMotivation($motivation)
    {
        $this->motivation = $motivation;

        return $this;
    }

    public function getMotivation()
    {
        return $this->motivation;
    }

    public function setVersionNumber($number)
    {
        $this->versionNumber = $number;

        return $this;
    }

    public function incrementVersionNumber()
    {
        $this->versionNumber = $this->versionNumber + 1;

        return $this;
    }

    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    public function setProposal(Proposal $proposal)
    {
        if ($this->proposal) {
            $this->proposal->removeArticle($this);
        }

        $this->proposal = $proposal;
        $this
            ->setNumber($proposal->getNumberOfPublishedArticles() + 1)
            ->snapshot()
        ;

        $proposal
            ->addArticle($this)
            ->incrementVersionNumber()
            ->snapshot()
        ;

        return $this;
    }

    public function getProposal()
    {
        return $this->proposal;
    }

    public function removeProposal()
    {
        $this->proposal = null;
    }

    public function addDiscussion(PublicDiscussion $discussion)
    {
        $this->discussions->add($discussion);

        return $this;
    }

    public function removeDiscussion(PublicDiscussion $discussion)
    {
        $this->discussions->removeElement($discussion);

        return $this;
    }

    public function getDiscussions()
    {
        return $this->discussions;
    }

    public function addToVersioning(ArticleVersion $articleVersion)
    {
        $this->versioning->add($articleVersion);

        return $this;
    }

    public function getVersioning()
    {
        return $this->versioning;
    }

    public function getLastVersion()
    {
        return $this->versioning->last();
    }

    public function snapshot()
    {
        $articleVersion = new ArticleVersion($this);
        $this->addToVersioning($articleVersion);

        return $this;
    }

    /**
     * This method is called on each published article of a proposal when the
     * author or the moderation decide to lock the proposal.
     */
    public function lock()
    {
        $this->setStatus($this::LOCKED);

        foreach ($this->discussions as $discussion) {
            $discussion->setLocked(True);
        }

        return $this;
    }

    /**
     * This method is called when the author of a proposal decides to remove
     * this article from the proposal.
     */
    public function remove()
    {
        $this->setStatus($this::REMOVED);

        foreach ($this->discussions as $discussion) {
            $discussion->setLocked(True);
        }

        $removedArticleNumber = $this->getNumber();
        $proposal             = $this->getProposal();

        foreach ($proposal->getArticles() as $article) {
            if ($article->isPublished()
                && $article->getNumber() > $removedArticleNumber
            ) {
                $article
                    ->decreaseNumber()
                    ->incrementVersionNumber()
                    ->snapshot()
                ;
            }
        }

        $proposal
            ->incrementVersionNumber()
            ->snapshot()
        ;

        return $this;
    }
}
