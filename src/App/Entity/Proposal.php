<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"slug","title"})
 * @ORM\HasLifecycleCallbacks
 */
class Proposal
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
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = 400,
     * )
     */
    private $abstract;

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
    private $motivation;

    /**
     * @ORM\Column(type="integer")
     */
    private $versionNumber;

    /**
     * If the proposal is published it is classified in a theme and visible by all the users.
     * The author is the only one that can publish it.
     *
     * @ORM\Column(type="boolean")
     */
    private $isPublished;

    /**
     * If the proposal is a wiki, every user can edit it.
     * The author is the only one that can make it a wiki.
     *
     * @ORM\Column(type="boolean")
     */
    private $isAWiki;

    /**
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="proposals", cascade={"persist"})
     * @Assert\Valid()
     */
    private $theme;

    /**
     * The author is initially the creator of the proposal.
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="proposals", cascade={"persist"})
     * @Assert\Valid()
     */
    private $author;

    /**
     * Users that claim their support to the proposal.
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="supportedProposals", cascade={"persist"})
     * @ORM\JoinTable(name="proposals_supporters")
     * @Assert\Valid()
     */
    private $supporters;

    /**
     * Users that claim their opposition to the proposal.
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="opposedProposals", cascade={"persist"})
     * @ORM\JoinTable(name="proposals_opposents")
     * @Assert\Valid()
     */
    private $opponents;

    /**
     * @ORM\OneToMany(targetEntity="OldProposal", mappedBy="recordedProposal", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $history;

    /**
     * @ORM\OneToMany(targetEntity="PublicDiscussion", mappedBy="proposal")
     * @Assert\Valid()
     */
    private $discussions;

    public function __construct()
    {
        $this->creationDate  = new \Datetime();
        $this->versionNumber = 1;
        $this->isPublished   = false;
        $this->isAWiki       = false;
        $this->supporters    = new ArrayCollection();
        $this->opponents     = new ArrayCollection();
        $this->oldProposals  = new ArrayCollection();
        $this->discussions   = new ArrayCollection();
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

    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    public function getAbstract()
    {
        return $this->abstract;
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

    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function isPublished()
    {
        return $this->isPublished;
    }

    public function setIsAWiki($isAWiki = true)
    {
        $this->isAWiki = $isAWiki;

        if ($isAWiki) {
            $this->author->removeProposal($this);
            $this->author = null;
        }

        return $this;
    }

    public function isAWiki()
    {
        return $this->isAWiki;
    }

    public function setTheme(Theme $theme)
    {
        if ($this->theme) {
            $this->theme->removeProposal($this);
        }

        $this->theme = $theme;
        $theme->addProposal($this);

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setAuthor(User $user = null)
    {
        if ($this->author) {
            $this->author->removeProposal($this);
        }

        $this->author = $user;
        if ($user) {
            $user->addProposal($this);
        }

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function addSupporter(User $supporter)
    {
        $this->supporters->add($supporter);
        $supporter->addSupportedProposal($this);

        return $this;
    }

    public function removeSupporter(User $supporter)
    {
        $this->supporters->removeElement($supporter);
        $supporter->removeSupportedProposal($this);

        return $this;
    }

    public function getSupporters()
    {
        return $this->supporters;
    }

    public function addOpponent(User $opponent)
    {
        $this->opponents->add($opponent);
        $opponent->addOpposedProposal($this);

        return $this;
    }

    public function removeOpponent(User $opponent)
    {
        $this->opponents->removeElement($opponent);
        $opponent->removeOpposedProposal($this);

        return $this;
    }

    public function getOpponents()
    {
        return $this->opponents;
    }

    public function addToHistory(OldProposal $oldProposal)
    {
        $this->history->add($oldProposal);

        return $this;
    }

    public function getHistory()
    {
        return $this->history;
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
}
