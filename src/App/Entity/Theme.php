<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThemeRepository")
 * @Gedmo\Tree(type="nested")
 * @UniqueEntity(fields="id")
 * @UniqueEntity(fields="slug")
 * @UniqueEntity(fields="title")
 * @ORM\HasLifecycleCallbacks
 */
class Theme
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
     * @ORM\Column(name="description", type="string", length=255)
     * @Assert\Length(
     *      min = "2",
     *      max = "255",
     *      minMessage = "Votre description doit au moins contenir {{ limit }} caractères",
     *      maxMessage = "Votre description ne doit pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $description;

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
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="children")
     * @ORM\JoinColumn(name="theme_parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Theme", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @Assert\Valid()
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="ThemeComment", mappedBy="theme")
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Proposal", mappedBy="theme")
     * @Assert\Valid()
     */
    private $proposals;

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

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->proposals = new ArrayCollection();
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

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
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

    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setParent(Theme $parent = null)
    {
        $this->parent = $parent;

        if ($parent) {
            $parent->addChild($this);
        }

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChild(Theme $child)
    {
        $this->children->add($child);

        return $this;
    }

    public function removeChild(Theme $child)
    {
        $this->children->removeElement($child);

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addComment(ThemeComment $comment)
    {
        $this->comments->add($comment);

        return $this;
    }

    public function removeComment(ThemeComment $comment)
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addProposal(Proposal $proposal)
    {
        $this->proposals->add($proposal);

        return $this;
    }

    public function removeProposal(Proposal $proposal)
    {
        $this->proposals->removeElement($proposal);

        return $this;
    }

    public function getProposals()
    {
        return $this->proposals;
    }

    public function isEmpty()
    {
        return $this->proposals->isEmpty();
    }

    /*****************************************************
     *****************************************************
     *
     * Methods to manage the image file associated to a theme
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
        return 'uploads/themes/images';
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

    /*****************************************************
     *****************************************************/
}
