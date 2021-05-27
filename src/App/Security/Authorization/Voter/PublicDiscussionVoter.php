<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\PublicDiscussion;
use App\Entity\User;

class PublicDiscussionVoter extends Voter
{
    const FOLLOW     = 'follow';
    const UNREADER   = 'unreader';
    const PUBLISHED  = 'published';
    const REMOVED    = 'removed';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::FOLLOW,
            self::UNREADER,
            self::PUBLISHED,
            self::REMOVED
        ])) {
            return false;
        }

        if (!$subject instanceof PublicDiscussion) {
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

        $publicDiscussion = $subject;

        switch ($attribute) {
            case self::FOLLOW:
                return $this->isFollowing($publicDiscussion, $user);
            case self::UNREADER:
                return $this->isUnreader($publicDiscussion, $user);
            case self::PUBLISHED:
                return $this->isPublished($publicDiscussion);
            case self::REMOVED:
                return $this->isRemoved($publicDiscussion);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function isFollowing($publicDiscussion, $user)
    {
        return $publicDiscussion->hasFollower($user);
    }

    private function isUnreader($publicDiscussion, $user)
    {
        return $publicDiscussion->hasUnreader($user);
    }

    private function isPublished($publicDiscussion)
    {
        return $publicDiscussion->getStatus() == $publicDiscussion::PUBLISHED;
    }

    private function isRemoved($publicDiscussion)
    {
        return $publicDiscussion->getStatus() == $publicDiscussion::REMOVED;
    }
}
