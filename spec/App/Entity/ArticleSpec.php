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
        $proposal->getNumberOfPublishedArticles()->willReturn(3);
        $proposal->addArticle($this)->willReturn($proposal);
        $proposal->incrementVersionNumber()->willReturn($proposal);
        $proposal->snapshot()->willReturn($proposal);

        $this->setProposal($proposal);

        $this->getProposal()->shouldBeAnInstanceOf(Proposal::class);

        $this->getNumber()->shouldReturn(4);
    }

    function it_takes_a_snapshot()
    {
        $this->snapshot();
        $this->getVersioning()->count()->shouldBe(1);

        $this->getVersioning()->shouldOnlyContainArticleVersions();
    }

    public function getMatchers() : array
    {
        return [
            'onlyContainArticleVersions' => function($subject) {
                foreach ($subject as $articleVersion) {
                    if (! $articleVersion instanceOf ArticleVersion) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
