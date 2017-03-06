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
 * @UniqueEntity(fields="slug")
 * @UniqueEntity(fields="title", message="Ce titre est déjà attribué")
 * @ORM\HasLifecycleCallbacks
 */
class Proposal
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
     *      max = "255",
     *      minMessage = "Votre titre doit au moins contenir {{ limit }} caractères",
     *      maxMessage = "Votre titre ne doit pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Vous devez faire un résumé de votre proposition")
     */
    private $abstract;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="N'oubliez pas de renseigner le contenu de votre proposition")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $editDate;

    /**
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="proposals", cascade={"persist"})
     * @Assert\Valid()
     */
    private $theme;

    /**
     * The main author can hire/fire side authors and edit the proposal.
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="mainProposals", cascade={"persist"})
     * @Assert\Valid()
     */
    private $mainAuthor;

    /**
     * The side authors can edit the proposal.
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="sideProposals", cascade={"persist"})
     * @ORM\JoinTable(name="proposals_sideAuthors")
     * @Assert\Valid()
     */
    private $sideAuthors;

    /**
     * If the proposal is public any user can edit the proposal.
     *
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * Users that claim their support to the proposal.
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="supportedProposals", cascade={"persist"})
     * @ORM\JoinTable(name="proposals_supportiveUsers")
     * @Assert\Valid()
     */
    private $supportiveUsers;

    /**
     * Users that claim their opposition to the proposal.
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="opposedProposals", cascade={"persist"})
     * @ORM\JoinTable(name="proposals_opposedUsers")
     * @Assert\Valid()
     */
    private $opposedUsers;

    /**
     * @ORM\OneToMany(targetEntity="ProposalVersion", mappedBy="proposal", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $versions;

    /**
     * @ORM\Column(type="integer")
     */
    private $versionNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * Stores the name of the image file associated to the theme
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * Stores temporarily the name of the old image file to delete it after the upload
     */
    private $temp;

    /**
     * @Assert\Image(
     *     minWidth = 128,
     *     maxWidth = 128,
     *     minHeight = 128,
     *     maxHeight = 128,
     *     mimeTypes = {"image/png"},
     *     maxSize = "1024k"
     * )
     */
    private $file;

    /**
     * @ORM\OneToMany(targetEntity="PublicDiscussion", mappedBy="proposal")
     * @Assert\Valid()
     */
    private $discussions;

    public function __construct()
    {
        $this->versionNumber = 1;
        $this->sideAuthors = new ArrayCollection();
        $this->supportiveUsers = new ArrayCollection();
        $this->opposedUsers = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->discussions = new ArrayCollection();
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

    public function setMainAuthor(User $user = null)
    {
        if ($this->mainAuthor) {
            $this->mainAuthor->removeMainProposal($this);
        }

        $this->mainAuthor = $user;
        if ($user) {
            $user->addMainProposal($this);
        }

        return $this;
    }

    public function removeMainAuthor()
    {
        if ($this->mainAuthor) {
            $this->mainAuthor->removeMainProposal($this);
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
        $sideAuthor->addSideProposal($this);

        return $this;
    }

    public function removeSideAuthor(User $sideAuthor)
    {
        $this->sideAuthors->removeElement($sideAuthor);
        $sideAuthor->removeSideProposal($this);

        return $this;
    }

    public function getSideAuthors()
    {
        return $this->sideAuthors;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setIsPublic()
    {
        if (($this->mainAuthor == null) and ($this->sideAuthors->isEmpty())) {
            $this->isPublic = true;
        } else {
            $this->isPublic = false;
        }

        return $this;
    }

    public function isPublic()
    {
        return $this->isPublic;
    }

    public function addSupportiveUser(User $supportiveUser)
    {
        $this->supportiveUsers->add($supportiveUser);
        $supportiveUser->addSupportedProposal($this);

        return $this;
    }

    public function removeSupportiveUser(User $supportiveUser)
    {
        $this->supportiveUsers->removeElement($supportiveUser);
        $supportiveUser->removeSupportedProposal($this);

        return $this;
    }

    public function getSupportiveUsers()
    {
        return $this->supportiveUsers;
    }

    public function addOpposedUser(User $opposedUser)
    {
        $this->opposedUsers->add($opposedUser);
        $opposedUser->addOpposedProposal($this);

        return $this;
    }

    public function removeOpposedUser(User $opposedUser)
    {
        $this->opposedUsers->removeElement($opposedUser);
        $opposedUser->removeOpposedProposal($this);

        return $this;
    }

    public function getOpposedUsers()
    {
        return $this->opposedUsers;
    }

    public function addVersion(ProposalVersion $version)
    {
        $this->versions->add($version);

        return $this;
    }

    public function removeVersion(ProposalVersion $version)
    {
        $this->versions->removeElement($version);

        return $this;
    }

    public function getVersions()
    {
        return $this->versions;
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

    public function editFromDraft(ProposalDraft $proposalDraft)
    {
        $this
            ->setTitle($proposalDraft->getTitle())
            ->setAbstract($proposalDraft->getAbstract())
            ->setContent($proposalDraft->getContent())
            ->setMainAuthor($proposalDraft->getMainAuthor())
        ;
        foreach ($proposalDraft->getSideAuthors() as $sideAuthor) {
            $this->addSideAuthor($sideAuthor);
        }
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

    /*****************************************************
     *****************************************************
     *
     * Methods to manage the image file associated to a proposal
     * http://symfony.com/doc/2.3/cookbook/doctrine/file_uploads.html
     *
     *****************************************************
     *****************************************************/

    /**
     * A convenience method that returns the absolute path to the file.
     */
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    /**
     * A convenience method that returns the web path
     * which can be used in a template to link to the uploaded file.
     */
    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/proposals/images';
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * Manage the path attribute
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {

            // compute a random name and try to guess the extension (more secure)
            $extension = $this->getFile()->guessExtension();
            if (!$extension) {
                // extension cannot be guessed
                $extension = 'bin';
            }

            $filename = $this->getTitle().rand(1, 99999).'.'.$extension;

            // set the path property to the filename where you've saved the file
            $this->path = $filename;
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * Uploads the image file after persisting the theme
     */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->path
        );

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     * Remove the image file after suppression of a theme
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }
}
