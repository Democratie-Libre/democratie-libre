<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Article;
use App\Entity\User;

class ArticleVoter extends Voter
{
    const CAN_BE_EDITED    = 'can_be_edited';
    const CAN_BE_REMOVED   = 'can_be_removed';
    const PUBLISHED        = 'published';
    const LOCKED           = 'locked';
    const REMOVED          = 'removed';
    const SHOW_ADMIN_PANEL = 'show_admin_panel';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::CAN_BE_EDITED,
            self::CAN_BE_REMOVED,
            self::PUBLISHED,
            self::LOCKED,
            self::REMOVED,
            self::SHOW_ADMIN_PANEL
        ])) {
            return false;
        }

        if (!$subject instanceof Article) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $article = $subject;

        switch ($attribute) {
            case self::PUBLISHED:
                return $this->isPublished($article);
            case self::LOCKED:
                return $this->isLocked($article);
            case self::REMOVED:
                return $this->isRemoved($article);
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CAN_BE_EDITED:
                return $this->canBeEdited($article, $user);
            case self::CAN_BE_REMOVED:
                return $this->canBeRemoved($article, $user);
            case self::SHOW_ADMIN_PANEL:
                return $this->showAdminPanel($article, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canBeEdited($article, $user)
    {
        if (!$this->isPublished($article)) {
            return false;
        }

        return $user === $article->getProposal()->getAuthor();
    }

    private function canBeRemoved($article, $user)
    {
        if (!$article->isPublished()) {
            return false;
        }

        return $user === $article->getProposal()->getAuthor();
    }

    private function isPublished($article)
    {
        return $article->getStatus() === $article::PUBLISHED;
    }

    private function isLocked($article)
    {
        return $article->getStatus() === $article::LOCKED;
    }

    private function isRemoved($article)
    {
        return $article->getStatus() === $article::REMOVED;
    }

    private function showAdminPanel($article, $user)
    {
        if (!$this->isPublished($article)) {
            return false;
        }

        return $user === $article->getProposal()->getAuthor();
    }
}
