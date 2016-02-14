<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="slug")
 * @UniqueEntity(fields="title")
 * @ORM\HasLifecycleCallbacks
 */
class ProposalVersion
{
    /**
     * @ORM\Column(name="id", type="integer", unique=true)
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
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     * @Assert\Length(
     *      min = "2",
     *      max = "255"
     * )
     */
    private $title;

    /**
     * @ORM\Column(name="abstract", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $abstract;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank(message="N'oubliez pas de renseigner le contenu de votre proposition")
     */
    private $content;

    /**
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     * @Assert\Valid()
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @Assert\Valid()
     */
    private $mainAuthor;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="versions_sideAuthors")
     * @Assert\Valid()
     */
    private $sideAuthors;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @Assert\Valid()
     */
    private $modifyingAuthor;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="versions_supportiveUsers")
     * @Assert\Valid()
     */
    private $supportiveUsers;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="versions_opposedUsers")
     * @Assert\Valid()
     */
    private $opposedUsers;

    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="versions")
     * @Assert\Valid()
     */
    private $proposal;

    /**
     * @ORM\Column(name="versionnumber", type="integer")
     */
    private $versionNumber;

    public function __construct()
    {
        $this->sideAuthors = new ArrayCollection();
        $this->supportiveUsers = new ArrayCollection();
        $this->opposedUsers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
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

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setDate()
    {
        $this->date = new \Datetime();
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setMainAuthor(User $user = null)
    {
        $this->mainAuthor = $user;

        return $this;
    }

    public function getMainAuthor()
    {
        return $this->mainAuthor;
    }

    public function addSideAuthor(User $user)
    {
        $this->sideAuthors->add($user);

        return $this;
    }

    public function removeSideAuthor(User $user)
    {
        $this->sideAuthors->removeElement($user);

        return $this;
    }

    public function getSideAuthors()
    {
        return $this->sideAuthors;
    }

    public function setModifyingAuthor(User $user)
    {
        $this->modifyingAuthor = $user;

        return $this;
    }

    public function getModifyingAuthor()
    {
        return $this->modifyingAuthor;
    }

    public function addSupportiveUser(User $user)
    {
        $this->supportiveUsers->add($user);

        return $this;
    }

    public function removeSupportiveUser(User $user)
    {
        $this->supportiveUsers->removeElement($user);

        return $this;
    }

    public function getSupportiveUsers()
    {
        return $this->supportiveUsers;
    }

    public function addOpposedUser(User $user)
    {
        $this->opposedUsers->add($user);

        return $this;
    }

    public function removeOpposedUser(User $user)
    {
        $this->opposedUsers->removeElement($user);

        return $this;
    }

    public function getOpposedUsers()
    {
        return $this->opposedUsers;
    }

    public function setProposal(Proposal $proposal)
    {
        $this->proposal = $proposal;
        $proposal->addVersion($this);

        return $this;
    }

    public function getProposal()
    {
        return $this->proposal;
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

    public function edit(Proposal $proposal, User $modifyingAuthor)
    {
        $proposalTitle = $proposal->getTitle();
        $proposalVersionNumber = $proposal->getVersionNumber();
        $format = '%s_v%s';

        $this
            ->setTitle(sprintf($format, $proposalTitle, $proposalVersionNumber))
            ->setAbstract($proposal->getAbstract())
            ->setContent($proposal->getContent())
            ->setTheme($proposal->getTheme())
            ->setMainAuthor($proposal->getMainAuthor())
            ->setModifyingAuthor($modifyingAuthor)
            ->setProposal($proposal)
            ->setVersionNumber($proposal->getVersionNumber())
        ;
        foreach ($proposal->getSupportiveUsers() as $user) {
            $this->addSupportiveUser($user);
        }
        foreach ($proposal->getOpposedUsers() as $user) {
            $this->addOpposedUser($user);
        }
        foreach ($proposal->getSideAuthors() as $user) {
            $this->addSideAuthor($user);
        }

        return $this;
    }
}
