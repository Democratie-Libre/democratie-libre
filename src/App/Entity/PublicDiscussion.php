<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class PublicDiscussion extends AbstractDiscussion
{
    const GLOBAL_DISCUSSION   = 'global_discussion';
    const THEME_DISCUSSION    = 'theme_discussion';
    const PROPOSAL_DISCUSSION = 'proposal_discussion';
    const ARTICLE_DISCUSSION  = 'article_discussion';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="discussions", cascade={"persist"})
     * @Assert\Valid()
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="discussions", cascade={"persist"})
     * @Assert\Valid()
     */
    private $proposal;

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="discussions", cascade={"persist"})
     * @Assert\Valid()
     */
    private $article;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="followedDiscussions", cascade={"persist"})
     * @ORM\JoinTable(name="public_discussions_followers")
     * @Assert\Valid()
     */
    private $followers;

    public function __construct()
    {
        parent::__construct();
        $this->followers = new ArrayCollection();
    }

    public function setType($type)
    {
        if ($type === self::GLOBAL_DISCUSSION) {
            $this
                ->setTheme(null)
                ->setProposal(null)
                ->setArticle(null);
        }

        if ($type === self::THEME_DISCUSSION) {
            $this
                ->setProposal(null)
                ->setArticle(null);
        }

        if ($type === self::PROPOSAL_DISCUSSION) {
            $this
                ->setTheme(null)
                ->setArticle(null);
        }

        if ($type === self::ARTICLE_DISCUSSION) {
            $this
                ->setTheme(null)
                ->setProposal(null);
        }

        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setTheme(Theme $theme = null)
    {
        if ($this->theme) {
            $this->theme->removeDiscussion($this);
            $this->theme = null;
        }

        if ($this->proposal) {
            $this->proposal->removeDiscussion($this);
            $this->proposal = null;
        }

        if ($this->article) {
            $this->article->removeDiscussion($this);
            $this->article = null;
        }

        if ($theme) {
            $this->theme = $theme;
            $theme->addDiscussion($this);
        }

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setProposal(Proposal $proposal = null)
    {
        if ($this->theme) {
            $this->theme->removeDiscussion($this);
            $this->theme = null;
        }

        if ($this->proposal) {
            $this->proposal->removeDiscussion($this);
            $this->proposal = null;
        }

        if ($this->article) {
            $this->article->removeDiscussion($this);
            $this->article = null;
        }

        if ($proposal) {
            $this->proposal = $proposal;
            $proposal->addDiscussion($this);
        }

        return $this;
    }

    public function getProposal()
    {
        return $this->proposal;
    }

    public function setArticle(Article $article = null)
    {
        if ($this->theme) {
            $this->theme->removeDiscussion($this);
            $this->theme = null;
        }

        if ($this->proposal) {
            $this->proposal->removeDiscussion($this);
            $this->proposal = null;
        }

        if ($this->article) {
            $this->article->removeDiscussion($this);
            $this->article = null;
        }

        if ($article) {
            $this->article = $article;
            $article->addDiscussion($this);
        }

        return $this;
    }

    public function getArticle()
    {
        return $this->article;
    }

    public function addFollower(User $follower)
    {
        if (false === $this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->addFollowedDiscussion($this);
        }

        return $this;
    }

    public function removeFollower(User $follower)
    {
        $this->followers->removeElement($follower);
        $follower->removeFollowedDiscussion($this);

        return $this;
    }

    public function hasFollower(User $follower)
    {
        return $this->followers->contains($follower);
    }

    public function getFollowers()
    {
        return $this->followers;
    }

    public function isGlobalDiscussion()
    {
       return $this->type === self::GLOBAL_DISCUSSION;
    }

    public function isThemeDiscussion()
    {
        return $this->type === self::THEME_DISCUSSION;
    }

    public function isProposalDiscussion()
    {
        return $this->type === self::PROPOSAL_DISCUSSION;
    }

    public function isArticleDiscussion()
    {
        return $this->type === self::ARTICLE_DISCUSSION;
    }

    public function resetUnreaders()
    {
        foreach ($this->followers as $follower) {
            $this->addUnreader($follower);
        }

        return $this;
    }
}
