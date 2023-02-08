<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use App\Entity\User;
use App\Entity\PrivateDiscussion;

class PrivateDiscussionVoter extends Voter
{
    const VIEW          = 'view';
    const CAN_BE_EDITED = 'can_be_edited';
    const CAN_BE_LOCKED = 'can_be_locked';
    const UNREADER      = 'unreader';

    private $doctrine;

    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::VIEW,
            self::CAN_BE_EDITED,
            self::CAN_BE_LOCKED,
            self::UNREADER
        ])) {
            return false;
        }

        if (!$subject instanceof PrivateDiscussion) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $privateDiscussion = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($privateDiscussion, $user);
            case self::CAN_BE_EDITED:
                return $this->canBeEdited($privateDiscussion, $user);
            case self::CAN_BE_LOCKED:
                return $this->canBeLocked($privateDiscussion, $user);
            case self::UNREADER:
                return $this->isUnreader($privateDiscussion, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView($privateDiscussion, $user)
    {
        return $privateDiscussion->hasMember($user);
    }

    private function canBeEdited($privateDiscussion, $user)
    {
        if ($privateDiscussion->isLocked()) {
            return false;
        }
        else {
            return $user === $privateDiscussion->getAdmin();
        }
    }

    private function canBeLocked($privateDiscussion, $user)
    {
        if ($privateDiscussion->isLocked()) {
            return false;
        }
        else {
            return $privateDiscussion->hasMember($user);
        }
    }

    private function isUnreader($privateDiscussion, $user)
    {
        return $privateDiscussion->hasUnreader($user);
    }
}
