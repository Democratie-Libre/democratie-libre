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

    protected function __construct()
    {
        parent::__construct();
        $this->status    = $this::PUBLISHED;
        $this->followers = new ArrayCollection();
    }

    public static function createGlobalDiscussion()
    {
        $discussion = new self();
        $discussion->type = self::GLOBAL_DISCUSSION;

        return $discussion;
    }

    public static function createThemeDiscussion(Theme $theme)
    {
        $discussion = new self();
        $discussion->setTheme($theme);

        return $discussion;
    }

    public static function createProposalDiscussion(Proposal $proposal)
    {
        $discussion = new self();
        $discussion->setProposal($proposal);

        return $discussion;
    }

    public static function createArticleDiscussion(Article $article)
    {
        $discussion = new self();
        $discussion->setArticle($article);

        return $discussion;
    }

    public function moveAs($type, $target = null)
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

        switch ($type) {
            case self::GLOBAL_DISCUSSION:
                $this->type = self::GLOBAL_DISCUSSION;
                break;
            case self::THEME_DISCUSSION:
                $this->setTheme($target);
                break;
            case self::PROPOSAL_DISCUSSION:
                $this->setProposal($target);
                break;
            case self::ARTICLE_DISCUSSION:
                $this->setArticle($target);
                break;
            default:
                throw new \Exception(sprintf(
                    '%s is not a valid type',
                    $type
                ));
        }

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    private function setTheme(Theme $theme)
    {
        $this->type = self::THEME_DISCUSSION;
        $this->theme = $theme;

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    private function setProposal(Proposal $proposal)
    {
        $this->type = self::PROPOSAL_DISCUSSION;
        $this->proposal = $proposal;

        return $this;
    }

    public function getProposal()
    {
        return $this->proposal;
    }

    private function setArticle(Article $article)
    {
        $this->type = self::ARTICLE_DISCUSSION;

        $this->article = $article;
        $article->addDiscussion($this);

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
