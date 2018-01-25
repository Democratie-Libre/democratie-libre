<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use App\Entity\User;
use App\Entity\PrivateDiscussion;

class PrivateDiscussionVoter extends Voter
{
    const VIEW       = 'view';
    const EDIT       = 'edit';
    const ADD_MEMBER = 'add_member';
    const UNREADER   = 'unreader';

    private $doctrine;

    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::ADD_MEMBER,
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
            case self::EDIT:
                return $this->canEdit($privateDiscussion, $user);
            case self::ADD_MEMBER:
                return $this->canAddMember($privateDiscussion, $user);
            case self::UNREADER:
                return $this->isUnreader($privateDiscussion, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView($privateDiscussion, $user)
    {
        return $privateDiscussion->hasMember($user);
    }

    private function canEdit($privateDiscussion, $user)
    {
        return $user === $privateDiscussion->getAdmin();
    }

    private function canAddMember($privateDiscussion, $user)
    {
        $isAdmin       = $user === $privateDiscussion->getAdmin();
        $usersNumber   = $this->doctrine->getRepository('App:User')->count();
        $membersNumber = count($privateDiscussion->getMembers());

        return $isAdmin and $usersNumber !== $membersNumber;
    }

    private function isUnreader($privateDiscussion, $user)
    {
        return $privateDiscussion->hasUnreader($user);
    }
}
