<?php

namespace spec\App\Entity;

use App\Entity\PublicDiscussion;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Proposal;
use App\Entity\Article;

class PublicDiscussionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PublicDiscussion::class);
    }

    function it_creates_a_global_discussion()
    {
        $this->beConstructedThrough('createGlobalDiscussion');

        $this->isGlobalDiscussion()->shouldBe(true);
    }

    function it_creates_a_theme_discussion()
    {
        $theme = new Theme();
        $this->beConstructedThrough('createThemeDiscussion', [$theme]);

        $this->isThemeDiscussion()->shouldBe(true);
    }

    function it_creates_a_proposal_discussion()
    {
        $proposal = new Proposal();
        $this->beConstructedThrough('createProposalDiscussion', [$proposal]);

        $this->isProposalDiscussion()->shouldBe(true);
    }

    function it_creates_an_article_discussion()
    {
        $article = new Article();
        $this->beConstructedThrough('createArticleDiscussion', [$article]);

        $this->isArticleDiscussion()->shouldBe(true);
        $this->getArticle()->shouldBe($article);
    }

    function it_moves_to_the_global_discussions()
    {
        $theme = new Theme();
        $this->beConstructedThrough('createThemeDiscussion', [$theme]);

        $this->moveAs(PublicDiscussion::GLOBAL_DISCUSSION);

        $this->isGlobalDiscussion()->shouldBe(true);
        $this->getTheme()->shouldBe(null);
        $this->getProposal()->shouldBe(null);
        $this->getArticle()->shouldBe(null);
    }

    function it_moves_a_discussion_to_a_theme()
    {
        $theme    = new Theme();
        $proposal = new Proposal();
        $this->beConstructedThrough('createProposalDiscussion', [$proposal]);

        $this->moveAs(PublicDiscussion::THEME_DISCUSSION, $theme);

        $this->isThemeDiscussion()->shouldBe(true);
        $this->getTheme()->shouldBe($theme);
        $this->getProposal()->shouldBe(null);
        $this->getArticle()->shouldBe(null);
    }

    function it_moves_a_discussion_to_a_proposal()
    {
        $proposal = new Proposal();
        $this->beConstructedThrough('createGlobalDiscussion');

        $this->moveAs(PublicDiscussion::PROPOSAL_DISCUSSION, $proposal);

        $this->isProposalDiscussion()->shouldBe(true);
        $this->getTheme()->shouldBe(null);
        $this->getProposal()->shouldBe($proposal);
        $this->getArticle()->shouldBe(null);
    }

    function it_moves_a_discussion_to_an_article()
    {
        $article       = new Article();
        $targetArticle = new Article();
        $this->beConstructedThrough('createArticleDiscussion', [$article]);

        $this->moveAs(PublicDiscussion::ARTICLE_DISCUSSION, $targetArticle);

        $this->isArticleDiscussion()->shouldBe(true);
        $this->getTheme()->shouldBe(null);
        $this->getProposal()->shouldBe(null);
        $this->getArticle()->shouldBe($targetArticle);
    }

    function it_gets_the_type_of_the_discussion()
    {
        $this->beConstructedThrough('createGlobalDiscussion');

        $this->getType()->shouldBe(PublicDiscussion::GLOBAL_DISCUSSION);
    }

    function it_adds_and_removes_a_follower()
    {
        $user = new User();
        $this->beConstructedThrough('createGlobalDiscussion');

        $this->addFollower($user);

        $this->hasFollower($user)->shouldBe(true);

        $this->removeFollower($user);
        $this->hasFollower($user)->shouldBe(false);
    }
}
