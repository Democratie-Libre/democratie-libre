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
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
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
     * @Assert\Valid()
     */
    private $proposal;

    /**
     * @ORM\OneToMany(targetEntity="PublicDiscussion", mappedBy="article")
     * @Assert\Valid()
     */
    private $discussions;

    /**
     * @ORM\OneToMany(targetEntity="ArticleVersion", mappedBy="recordedArticle", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $versioning;

    public function __construct()
    {
        $this->creationDate  = new \Datetime();
        $this->versionNumber = 1;
        $this->discussions   = new ArrayCollection();
        $this->versioning    = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
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
        $this->lastEditDate = new \Datetime();

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
        $proposal->addArticle($this);

        return $this;
    }

    public function getProposal()
    {
        return $this->proposal;
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
}
