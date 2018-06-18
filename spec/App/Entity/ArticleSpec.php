<?php

namespace spec\App\Entity;

use App\Entity\Article;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Entity\Proposal;
use App\Entity\ArticleVersion;

class ArticleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Article::class);
    }

    function it_increments_version_number()
    {
        $this->getVersionNumber()->shouldBe(1);
        $this->incrementVersionNumber();
        $this->getVersionNumber()->shouldBe(2);
    }

    function it_sets_a_proposal(Proposal $proposal)
    {
        $proposal->getNumberOfArticles()->willReturn(3);
        $proposal->addArticle($this)->willReturn($proposal);
        $proposal->incrementVersionNumber()->willReturn($proposal);
        $proposal->snapshot()->willReturn($proposal);

        $this->setProposal($proposal);

        if (! $this->getProposal()->getWrappedObject() instanceOf Proposal) {
            throw new Exception('Proposal does not contain a Proposal entity !');
        }

        $this->getNumber()->shouldReturn(4);
    }

    function it_takes_a_snapshot()
    {
        $this->snapshot();
        $this->getVersioning()->count()->shouldBe(1);
        if (! $this->getVersioning()->getWrappedObject()[0] instanceOf ArticleVersion) {
            throw new Exception('Versioning does not contain an ArticleVersion entity !');
        }
    }
}
