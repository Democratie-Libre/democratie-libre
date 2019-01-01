<?php

namespace spec\App\Entity;

use App\Entity\PrivateDiscussion;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Entity\User;

class PrivateDiscussionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PrivateDiscussion::class);
    }

    function it_sets_the_admin(User $user)
    {
        $user->addAdminDiscussion($this)->shouldBeCalled();
        $user->addUnreadDiscussion($this)->shouldBeCalled();
        $user->addPrivateDiscussion($this)->willReturn($user);

        $this->setAdmin($user);

        $this->getAdmin()->shouldReturn($user);
    }

    function it_adds_a_member(User $user)
    {
        $user->addUnreadDiscussion($this)->willReturn(null);
        $user->addPrivateDiscussion($this)->willReturn($user);

        $this->addMember($user);

        $this->getMembers()->shouldContain($user);
        $this->getUnreaders()->shouldContain($user);
    }

    function it_removes_a_member(User $user)
    {
        $user->removePrivateDiscussion($this)->willReturn(null);

        $this->removeMember($user);

        $this->getMembers()->shouldNotContain($user);
    }

    function it_reset_the_unreaders(User $user)
    {
        $user->addUnreadDiscussion($this)->willReturn(null);
        $user->addPrivateDiscussion($this)->willReturn($user);
        $user->removeUnreadDiscussion($this)->willReturn(null);

        $this->addMember($user);
        $this->removeUnreader($user);

        $this->resetUnreaders();

        $this->getUnreaders()->shouldContain($user);
    }
}
