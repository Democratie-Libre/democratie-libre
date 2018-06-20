<?php

namespace spec\App\Security\Authorization\Voter;

use App\Security\Authorization\Voter\ArticleVoter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\Proposal;

class ArticleVoterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ArticleVoter::class);
    }

    function let(AccessDecisionManagerInterface $decisionManager)
    {
        $this->beConstructedWith($decisionManager);
    }

    function it_votes_for_newly_created_token_and_article(TokenInterface $token, Article $article)
    {
        $this
            ->vote($token, $article, ['edit'])
            ->shouldReturn(-1)
        ;
    }

    function it_votes_for_a_wrong_attribute(TokenInterface $token, Article $article)
    {
        $this
            ->vote($token, $article, ['plouf'])
            ->shouldReturn(0)
        ;
    }

    function it_votes_for_matching_author_and_article(TokenInterface $token, Article $article, User $author, Proposal $proposal)
    {
        $token->getUser()->willReturn($author);
        $article->getProposal()->willReturn($proposal);
        $proposal->getAuthor()->willReturn($author);

        $this
            ->vote($token, $article, ['edit'])
            ->shouldReturn(1)
        ;
    }

    function it_votes_for_deletion_by_the_author(TokenInterface $token, Article $article, User $author, Proposal $proposal)
    {
        $token->getUser()->willReturn($author);
        $article->getProposal()->willReturn($proposal);
        $proposal->getAuthor()->willReturn($author);

        $this
            ->vote($token, $article, ['delete'])
            ->shouldReturn(1)
        ;
    }

    function it_votes_for_deletion_by_any_user(TokenInterface $token, Article $article, User $author, User $user, Proposal $proposal)
    {
        $token->getUser()->willReturn($user);
        $article->getProposal()->willReturn($proposal);
        $proposal->getAuthor()->willReturn($author);

        $this
            ->vote($token, $article, ['delete'])
            ->shouldReturn(-1)
        ;
    }

    function it_votes_for_deletion_by_the_admin(TokenInterface $token, Article $article, User $author, User $admin, Proposal $proposal)
    {
        $token->getUser()->willReturn($admin);
        $token->getRoles()->willReturn(['ROLE_ADMIN']);
        $article->getProposal()->willReturn($proposal);
        $proposal->getAuthor()->willReturn($author);

        $this
            ->vote($token, $article, ['delete'])
            ->shouldReturn(1)
        ;
    }
}
