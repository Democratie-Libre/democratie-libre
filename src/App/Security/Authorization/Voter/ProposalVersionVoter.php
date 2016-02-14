<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProposalVersionVoter implements VoterInterface
{
    const FIRSTVERSION = 'first_version';
    const LASTVERSION  = 'last_version';

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::FIRSTVERSION,
            self::LASTVERSION,
        ));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\ProposalVersion';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $version, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($version))) {
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

        $versionNumber = $version->getVersionNumber();
        $proposalVersionNumber = $version->getProposal()->getVersionNumber();

        switch ($attribute) {
            case self::FIRSTVERSION:
                if ($versionNumber === 1) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::LASTVERSION:
                if ($versionNumber + 1 === $proposalVersionNumber) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
