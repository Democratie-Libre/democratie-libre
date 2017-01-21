<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PublicDiscussionVoter implements VoterInterface
{
    const FOLLOW   = 'follow';
    const UNREADER = 'unreader';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::FOLLOW,
            self::UNREADER,
        ));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\PublicDiscussion';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $discussion, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($discussion))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correctly, only allow one attribute
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed'
            );
        }

        // set the attribute to check against
        $attribute = $attributes[0];

        // check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        $isFollower = $discussion->hasFollower($user);
        $isUnreader = $discussion->hasUnreader($user);

        switch ($attribute) {
            case self::FOLLOW:
                if ($isFollower) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::UNREADER:
                if ($isUnreader) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
