<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MessageVoter implements VoterInterface
{
    const VIEW         = 'view';
    const FIRSTREADING = 'first_reading';
    const REPLY        = 'reply';

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::VIEW,
            self::FIRSTREADING,
            self::REPLY,
        ));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\Message';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $message, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($message))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correct, only allow one attribute
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW or EDIT'
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

        $isSender    = ($message->getSender() === $user);
        $isAddressee = $message->hasAddressee($user);
        $isReader    = $message->hasReader($user);

        switch ($attribute) {
            case self::VIEW:
                if ($isSender or $isAddressee) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::FIRSTREADING:
                if ($isAddressee and (!$isReader)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::REPLY:
                if ($isSender or $isAddressee) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
