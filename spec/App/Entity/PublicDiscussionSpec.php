<?php

namespace spec\App\Entity;

use App\Entity\PublicDiscussion;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Entity\User;

class PublicDiscussionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PublicDiscussion::class);
    }

    function it_adds_a_follower()
    {
        $user = new User();

        $this->addFollower($user);
        $this->hasFollower($user)->shouldBe(true);
    }

    function it_is_a_global_discussion_by_default()
    {
        $this->isGlobalDiscussion()->shouldBe(true);
    }
}
