<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Article;
use App\Entity\User;

class ArticleVoter extends Voter
{
    const EDIT          = 'edit';
    const USER_CAN_LOCK = 'user_can_lock';
    const PUBLISHED     = 'published';
    const LOCKED        = 'locked';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::EDIT,
            self::USER_CAN_LOCK,
            self::PUBLISHED,
            self::LOCKED
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
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($article, $user);
            case self::USER_CAN_LOCK:
                return $this->userCanLock($article, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit($article, $user)
    {
        return $user === $article->getProposal()->getAuthor();
    }

    private function userCanLock($article, $user)
    {
        return $user === $article->getProposal()->getAuthor();
    }

    private function isPublished($article)
    {
        return $article->getStatus() == $article::PUBLISHED;
    }

    private function isLocked($article)
    {
        return $article->getStatus() == $article::LOCKED;
    }
}
