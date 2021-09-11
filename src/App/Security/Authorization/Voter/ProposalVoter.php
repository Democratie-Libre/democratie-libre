<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Proposal;
use App\Entity\User;

class ProposalVoter extends Voter
{
    const CAN_BE_EDITED    = 'can_be_edited';
    const AUTHOR           = 'author';
    const SUPPORTER        = 'supporter';
    const OPPONENT         = 'opponent';
    const NEUTRAL          = 'neutral';
    const PUBLISHED        = 'published';
    const LOCKED           = 'locked';
    const CAN_BE_LOCKED    = 'can_be_locked';
    const CAN_BE_MOVED     = 'can_be_moved';
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
            self::AUTHOR,
            self::SUPPORTER,
            self::OPPONENT,
            self::NEUTRAL,
            self::PUBLISHED,
            self::LOCKED,
            self::CAN_BE_LOCKED,
            self::CAN_BE_MOVED,
            self::SHOW_ADMIN_PANEL
        ])) {
            return false;
        }

        if (!$subject instanceof Proposal) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $proposal = $subject;

        switch ($attribute) {
            case self::PUBLISHED:
                return $this->isPublished($proposal);
            case self::LOCKED:
                return $this->isLocked($proposal);
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CAN_BE_EDITED:
                return $this->canBeEdited($proposal, $user, $token);
            case self::AUTHOR:
                return $this->isAuthor($proposal, $user);
            case self::SUPPORTER:
                return $this->isSupporter($proposal, $user);
            case self::OPPONENT:
                return $this->isOpponent($proposal, $user);
            case self::NEUTRAL:
                return $this->isNeutral($proposal, $user);
            case self::CAN_BE_LOCKED:
                return $this->canBeLocked($proposal, $user, $token);
            case self::CAN_BE_MOVED:
                return $this->canBeMoved($proposal, $user, $token);
            case self::SHOW_ADMIN_PANEL:
                return $this->showAdminPanel($proposal, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canBeEdited($proposal, $user, $token)
    {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($this->isAuthor($proposal, $user)) {
            return true;
        }

        return $proposal->isAWiki();
    }

    private function isAuthor($proposal, $user)
    {
        return $user === $proposal->getAuthor();
    }

    private function isSupporter($proposal, $user)
    {
        return $proposal->getSupporters()->contains($user);
    }

    private function isOpponent($proposal, $user)
    {
        return $proposal->getOpponents()->contains($user);
    }

    private function isNeutral($proposal, $user)
    {
        return !$this->isSupporter($proposal, $user) and !$this->isOpponent($proposal, $user);
    }

    private function isPublished($proposal)
    {
        return $proposal->getStatus() == $proposal::PUBLISHED;
    }

    private function isLocked($proposal)
    {
        return $proposal->getStatus() == $proposal::LOCKED;
    }

    private function canBeLocked($proposal, $user, $token)
    {
        if ($this->isLocked($proposal)) {
            return false;
        }

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($this->isAuthor($proposal, $user)) {
            return true;
        }

        return false;
    }

    private function canBeMoved($proposal, $user, $token)
    {
        if ($this->isLocked($proposal)) {
            return false;
        }

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return false;
    }

    private function showAdminPanel($proposal, $user, $token)
    {
        if ($this->isLocked($proposal)) {
            return false;
        }

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($this->isAuthor($proposal, $user)) {
            return true;
        }

        return false;
    }
}
