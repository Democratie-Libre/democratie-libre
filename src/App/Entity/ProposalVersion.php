<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This entity allows to record the versions of the proposals.
 * It is instanciated each time a proposal is modified.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProposalVersionRepository")
 * @UniqueEntity(fields={"slug"})
 * @ORM\HasLifecycleCallbacks
 */
class ProposalVersion
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
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/[a-zA-Z0-9]+/")
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
    private $snapDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motivation;

    /**
     * @ORM\ManyToMany(targetEntity="ArticleVersion", inversedBy="proposalVersions")
     */
    private $articleVersions;

    /**
     * @ORM\Column(type="integer")
     */
    private $versionNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAWiki;

    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="versioning")
     */
    private $recordedProposal;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      max = 100,
     * )
     */
    private $themeTitle;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="proposalVersions_supporters")
     */
    private $supporters;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="proposalVersions_opponents")
     */
    private $opponents;

    public function __construct(Proposal $proposal)
    {
        $this->title            = $proposal->getTitle();
        $this->abstract         = $proposal->getAbstract();
        $this->snapDate         = new \DateTime();
        $this->motivation       = $proposal->getMotivation();
        $this->articleVersions  = new ArrayCollection();
        $this->versionNumber    = $proposal->getVersionNumber();
        $this->isAWiki          = $proposal->isAWiki();
        $this->recordedProposal = $proposal;
        $this->themeTitle       = $proposal->getTheme()->getTitle();
        $this->author           = $proposal->getAuthor();
        $this->supporters       = $proposal->getSupporters();
        $this->opponents        = $proposal->getOpponents();

        foreach ($proposal->getArticles() as $article) {
            if ($article->isPublished()) {
                $articleLastVersion = $article->getLastVersion();
                $this->articleVersions->add($articleLastVersion);
                $articleLastVersion->addProposalVersion($this);
            }
        }
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

    public function getAbstract()
    {
        return $this->abstract;
    }

    public function getSnapDate()
    {
        return $this->snapDate;
    }

    public function getMotivation()
    {
        return $this->motivation;
    }

    public function getArticleVersions()
    {
        return $this->articleVersions;
    }

    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    public function isFirstVersion()
    {
        return $this->getVersionNumber() === 1;
    }

    public function isLastVersion()
    {
        $thisVersionNumber             = $this->getVersionNumber();
        $recordedProposalVersionNumber = $this->getRecordedProposal()
            ->getVersionNumber()
        ;

        return $thisVersionNumber === $recordedProposalVersionNumber;
    }

    public function isAWiki()
    {
        return $this->isAWiki;
    }

    public function getRecordedProposal()
    {
        return $this->recordedProposal;
    }

    public function getThemeTitle()
    {
        return $this->themeTitle;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getSupporters()
    {
        return $this->supporters;
    }

    public function getOpponents()
    {
        return $this->opponents;
    }
}
