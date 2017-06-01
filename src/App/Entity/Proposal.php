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
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
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
     * @ORM\Column(type="text")
     */
    private $argumentation;

    /**
     * @ORM\Column(type="text")
     */
    private $executionProcedure;

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

    public function setArgumentation($argumentation)
    {
        $this->argumentation = $argumentation;

        return $this;
    }

    public function getArgumentation()
    {
        return $this->argumentation;
    }

    public function setExecutionProcedure($executionProcedure)
    {
        $this->executionProcedure = $executionProcedure;

        return $this;
    }

    public function getExecutionProcedure()
    {
        return $this->executionProcedure;
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
