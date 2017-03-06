<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class PrivateDiscussionVoter implements VoterInterface
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

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::VIEW,
            self::EDIT,
            self::ADD_MEMBER,
            self::UNREADER,
        ));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\PrivateDiscussion';

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

        $isMember      = $discussion->hasMember($user);
        $isAdmin       = $user === $discussion->getAdmin();
        $isUnreader    = $discussion->hasUnreader($user);
        $usersNumber   = $this->doctrine->getRepository('App:User')->count();
        $membersNumber = count($discussion->getMembers());

        switch ($attribute) {
            case self::VIEW:
                if ($isMember) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::EDIT:
                if ($isAdmin) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::ADD_MEMBER:
                if ($isAdmin and $usersNumber !== $membersNumber) {
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
