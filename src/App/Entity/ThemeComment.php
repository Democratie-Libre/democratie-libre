<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ThemeComment
{
    /**
     * @ORM\Column(name="id", type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank(message="Veuillez renseigner le contenu de votre commentaire")
     */
    private $content;

    /**
     * @ORM\Column(name="creationDate", type="datetime")
     * @Assert\DateTime()
     */
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid()
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="comments")
     * @ORM\JoinColumn(name="theme_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @Assert\Valid()
     */
    private $theme;

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
    public function updateCreationDate()
    {
        $this->creationDate = new \Datetime();
    }

    public function getCreationDate()
    {
        return $this->creationDate;
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

    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;
        $theme->addComment($this);

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }
}
