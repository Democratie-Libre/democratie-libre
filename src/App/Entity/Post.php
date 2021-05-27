<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Post
{
    /**
     * @ORM\Column(type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="AbstractDiscussion", inversedBy="posts")
     */
    private $discussion;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    public function getId()
    {
        return $this->id;
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
    public function updateDate()
    {
        $this->date = new \DateTime();
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDiscussion(AbstractDiscussion $discussion)
    {
        if ($this->discussion) {
            $this->discussion->removePost($this);
        }

        $this->discussion = $discussion;
        $discussion->addPost($this);

        return $this;
    }

    public function getDiscussion()
    {
        return $this->discussion;
    }

    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}
