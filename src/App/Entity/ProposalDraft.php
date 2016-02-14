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
 * @UniqueEntity(fields="title", message="Ce titre est déjà attribué")
 * @ORM\HasLifecycleCallbacks
 */
class ProposalDraft
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
     *      max = "255",
     *      minMessage = "Votre titre doit au moins contenir {{ limit }} caractères",
     *      maxMessage = "Votre titre ne doit pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(name="abstract", type="string", length=255)
     * @Assert\NotBlank(message="Vous devez faire un résumé de votre proposition")
     */
    private $abstract;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank(message="N'oubliez pas de renseigner le contenu de votre proposition")
     */
    private $content;

    /**
     * @ORM\Column(name="creationDate", type="datetime")
     * @Assert\DateTime()
     */
    private $creationDate;

    /**
     * @ORM\Column(name="editDate", type="datetime")
     * @Assert\DateTime()
     */
    private $editDate;

    /**
     * The main author can hire/fire side authors and edit/delete the proposal draft.
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="mainProposalDrafts", cascade={"persist"})
     * @Assert\Valid()
     */
    private $mainAuthor;

    /**
     * The side authors can edit the proposal draft.
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="sideProposalDrafts", cascade={"persist"})
     * @ORM\JoinTable(name="ProposalDrafts_sideAuthors")
     * @Assert\Valid()
     */
    private $sideAuthors;

    public function __construct()
    {
        $this->sideAuthors = new ArrayCollection();
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
    public function setCreationDate()
    {
        $this->creationDate = new \Datetime();

        return $this;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setEditDate()
    {
        $this->editDate = new \Datetime();

        return $this;
    }

    public function getEditDate()
    {
        return $this->editDate;
    }

    public function setMainAuthor(User $user = null)
    {
        if ($this->mainAuthor) {
            $this->mainAuthor->removeMainProposalDraft($this);
        }

        $this->mainAuthor = $user;
        if ($user) {
            $user->addMainProposalDraft($this);
        }

        return $this;
    }

    public function removeMainAuthor()
    {
        if ($this->mainAuthor) {
            $this->mainAuthor->removeMainProposalDraft($this);
        }

        $this->mainAuthor = null;

        return $this;
    }

    public function getMainAuthor()
    {
        return $this->mainAuthor;
    }

    public function addSideAuthor(User $sideAuthor)
    {
        $this->sideAuthors->add($sideAuthor);
        $sideAuthor->addSideProposalDraft($this);

        return $this;
    }

    public function removeSideAuthor(User $sideAuthor)
    {
        $this->sideAuthors->removeElement($sideAuthor);
        $sideAuthor->removeSideProposalDraft($this);

        return $this;
    }

    public function getSideAuthors()
    {
        return $this->sideAuthors;
    }
}
