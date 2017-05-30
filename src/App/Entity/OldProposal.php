<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This entity allows to record the history of the proposals.
 * It is instanciated each time a proposal is modified.
 *
 * @ORM\Entity(repositoryClass="App\Repository\OldProposalRepository")
 * @UniqueEntity(fields={"slug","oldTitle"})
 * @ORM\HasLifecycleCallbacks
 */
class OldProposal
{
    /**
     * @ORM\Column(type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"oldTitle"})
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
    private $oldTitle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $oldAbstract;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $snapDate;

    /**
     * @ORM\Column(type="text")
     */
    private $oldArgumentation;

    /**
     * @ORM\Column(type="text")
     */
    private $oldExecutionProcedure;

    /**
     * @ORM\Column(type="integer")
     */
    private $oldVersionNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wasPublished;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wasAWiki;

    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="history")
     * @Assert\Valid()
     */
    private $recordedProposal;

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     * @Assert\Valid()
     */
    private $oldTheme;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @Assert\Valid()
     */
    private $oldAuthor;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="oldproposals_supporters")
     * @Assert\Valid()
     */
    private $oldSupporters;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="oldproposals_opponents")
     * @Assert\Valid()
     */
    private $oldOpponents;

    public function __construct(Proposal $proposal)
    {
        $this->oldTitle              = sprintf('%s_v%s', $proposal->getTitle(), $proposal->getVersionNumber());
        $this->oldAbstract           = $proposal->getAbstract();
        $this->snapDate              = new \Datetime(); 
        $this->oldArgumentation      = $proposal->getArgumentation();
        $this->oldExecutionProcedure = $proposal->getExecutionProcedure();
        $this->oldVersionNumber      = $proposal->getVersionNumber();
        $this->wasPublished          = $proposal->isPublished();
        $this->wasAWiki              = $proposal->isAWiki();
        $this->recordedProposal      = $proposal;
        $this->oldTheme              = $proposal->getTheme();
        $this->oldAuthor             = $proposal->getAuthor();
        $this->oldSupporters         = $proposal->getSupporters();
        $this->oldOpponents          = $proposal->getOpponents();
        $proposal->addToHistory($this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getOldTitle()
    {
        return $this->oldTitle;
    }

    public function getOldAbstract()
    {
        return $this->oldAbstract;
    }

    public function getSnapDate()
    {
        return $this->snapDate;
    }

    public function getOldArgumentation()
    {
        return $this->oldArgumentation;
    }

    public function getOldExecutionProcedure()
    {
        return $this->oldExecutionProcedure;
    }

    public function getOldVersionNumber()
    {
        return $this->oldVersionNumber;
    }

    public function wasPublished()
    {
        return $this->wasPublished;
    }

    public function wasAWiki()
    {
        return $this->wasAWiki;
    }

    public function getRecordedProposal()
    {
        return $this->recordedProposal;
    }

    public function getOldTheme()
    {
        return $this->oldTheme;
    }

    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    public function getOldSupporters()
    {
        return $this->oldSupporters;
    }

    public function getOldOpponents()
    {
        return $this->oldOpponents;
    }
}
