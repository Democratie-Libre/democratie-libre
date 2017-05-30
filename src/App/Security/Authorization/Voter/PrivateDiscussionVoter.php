<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use App\Entity\User;

class PrivateDiscussionVoter extends AbstractVoter
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

    public function getSupportedAttributes()
    {
        return array(
            self::VIEW,
            self::EDIT,
            self::ADD_MEMBER,
            self::UNREADER,
        );
    }

    public function getSupportedClasses()
    {
        return array('App\Entity\PrivateDiscussion');
    }

    public function isGranted($attribute, $discussion, $user = null)
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$user instanceof User) {
            throw new \LogicException(
                'The user is somehow not our User class!'
            );
        }

        $isMember      = $discussion->hasMember($user);
        $isAdmin       = $user === $discussion->getAdmin();
        $isUnreader    = $discussion->hasUnreader($user);
        $usersNumber   = $this->doctrine->getRepository('App:User')->count();
        $membersNumber = count($discussion->getMembers());

        switch ($attribute) {
            case self::VIEW:
                if ($isMember) {
                    return true;
                }
                break;
            case self::EDIT:
                if ($isAdmin) {
                    return true;
                }
                break;
            case self::ADD_MEMBER:
                if ($isAdmin and $usersNumber !== $membersNumber) {
                    return true;
                }
                break;
            case self::UNREADER:
                if ($isUnreader) {
                    return true;
                }
                break;
        }

        return false;
    }
}
