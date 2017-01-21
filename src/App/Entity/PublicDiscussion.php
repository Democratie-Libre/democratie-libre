<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PublicDiscussionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PublicDiscussion extends AbstractDiscussion
{
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
     * @ORM\ManyToMany(targetEntity="User", inversedBy="followedDiscussions", cascade={"persist"})
     * @ORM\JoinTable(name="public_discussions_followers")
     * @Assert\Valid()
     */
    private $followers;

    public function __construct()
    {
        parent::__construct();
        $this->followers  = new ArrayCollection();
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

    public function globalDiscussion()
    {
       return (($this->theme === null) and ($this->proposal === null));
    }

    public function themeDiscussion()
    {
        return !($this->theme === null);
    }

    public function proposalDiscussion()
    {
        return !($this->proposal === null);
    }

    public function resetUnreaders()
    {
        foreach ($this->followers as $follower) {
            $this->addUnreader($follower);
        }

        return $this;
    }
}
