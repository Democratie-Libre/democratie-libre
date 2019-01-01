<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 */
class PrivateDiscussion extends AbstractDiscussion
{
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="adminDiscussions", cascade={"persist"})
     * @Assert\Valid()
     */
    private $admin;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="privateDiscussions", cascade={"persist"})
     * @ORM\JoinTable(name="private_discussions_members")
     * @Assert\Valid()
     */
    private $members;

    protected function __construct()
    {
        parent::__construct();
        $this->members = new ArrayCollection();
    }

    public static function create()
    {
        return new self();
    }

    public function setAdmin(User $user)
    {
        if ($this->admin) {
            $this->admin->removeAdminDiscussion($this);
        }

        $this->admin = $user;
        $this->addMember($user);
        $user->addAdminDiscussion($this);

        return $this;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    public function addMember(User $member)
    {
        if (false === $this->members->contains($member)) {
            $this->members->add($member);
            $this->addUnreader($member);
            $member->addPrivateDiscussion($this)->addUnreadDiscussion($this);
        }

        return $this;
    }

    public function removeMember(User $member)
    {
        $this->members->removeElement($member);
        $member->removePrivateDiscussion($this);

        return $this;
    }

    public function hasMember(User $member)
    {
        return $this->members->contains($member);
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function resetUnreaders()
    {
        foreach ($this->members as $member) {
            $this->addUnreader($member);
        }

        return $this;
    }
}
