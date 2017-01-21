<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"abstract_discussion" = "AbstractDiscussion", "public_discussion" = "PublicDiscussion", "private_discussion" = "PrivateDiscussion"})
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields="slug")
 */
class AbstractDiscussion
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
    protected $slug;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(
     *      min = "2",
     *      max = "255"
     * )
     */
    protected $title;

    /**
     * @ORM\Column(name="abstract", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $abstract;

    /**
     * @ORM\Column(name="creationDate", type="datetime")
     * @Assert\DateTime()
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="lastEditDate", type="datetime")
     * @Assert\DateTime()
     */
    protected $lastEditDate;

    /**
     * If the discussion is locked the users cannot comment but can still access it.
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="discussion", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    protected $posts;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="unreadDiscussions", cascade={"persist"})
     * @ORM\JoinTable(name="discussions_unreaders")
     * @Assert\Valid()
     */
    protected $unreaders;

    public function __construct()
    {
        $this->locked = false;
        $this->posts  = new ArrayCollection();
        $this->unreaders = new ArrayCollection();
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
    public function setLastEditDate()
    {
        $this->lastEditDate = new \Datetime();

        return $this;
    }

    public function getLastEditDate()
    {
        return $this->lastEditDate;
    }

    public function setLocked($bool)
    {
        $this->locked = $bool;

        return $this;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function addPost(Post $post)
    {
        $this->posts->add($post);

        return $this;
    }

    public function removePost(Post $post)
    {
        $this->posts->removeElement($post);

        return $this;
    }

    public function hasPost(Post $post)
    {
        return $this->posts->contains($post);
    }

    public function getPosts()
    {
        return $this->posts;
    }

    public function addUnreader(User $unreader)
    {
        if (false === $this->unreaders->contains($unreader)) {
            $this->unreaders->add($unreader);
            $unreader->addUnreadDiscussion($this);
        }

        return $this;
    }

    public function removeUnreader(User $unreader)
    {
        $this->unreaders->removeElement($unreader);
        $unreader->removeUnreadDiscussion($this);

        return $this;
    }

    public function hasUnreader(User $unreader)
    {
        return $this->unreaders->contains($unreader);
    }

    public function getUnreaders()
    {
        return $this->unreaders;
    }
}
