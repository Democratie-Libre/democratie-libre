<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class PublicDiscussionRepository extends EntityRepository
{
    public function findGlobalDiscussions()
    {
        $discussions = $this->findAll();
        $globalDiscussions = [];

        foreach ($discussions as $discussion) {
            if ($discussion->globalDiscussion()) {
                $globalDiscussions[] = $discussion;
            }
        }

        return $globalDiscussions;
    }

    public function findThemeDiscussions(Theme $theme)
    {
        $discussions = $this->findAll();
        $themeDiscussions = [];

        foreach ($discussions as $discussion) {
            if ($discussion->getTheme() === $theme) {
                $themeDiscussions[] = $discussion;
            }
        }

        return $themeDiscussions;
    }

    public function findProposalDiscussions(Proposal $proposal)
    {
        $discussions = $this->findAll();
        $proposalDiscussions = [];

        foreach ($discussions as $discussion) {
            if ($discussion->getProposal() === $proposal) {
                $proposalDiscussions[] = $discussion;
            }
        }

        return $proposalDiscussions;
    }
}
