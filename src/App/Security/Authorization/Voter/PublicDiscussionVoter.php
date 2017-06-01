<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class PublicDiscussionVoter extends AbstractVoter
{
    const FOLLOW   = 'follow';
    const UNREADER = 'unreader';

    public function getSupportedAttributes()
    {
        return array(
            self::FOLLOW,
            self::UNREADER,
        );
    }

    public function getSupportedClasses()
    {
        return array('App\Entity\PublicDiscussion');
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

        $isFollower = $discussion->hasFollower($user);
        $isUnreader = $discussion->hasUnreader($user);

        switch ($attribute) {
            case self::FOLLOW:
                if ($isFollower) {
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
