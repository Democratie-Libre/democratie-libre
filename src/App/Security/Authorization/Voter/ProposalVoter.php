<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProposalVoter implements VoterInterface
{
    const EDIT       = 'edit';
    const MAINAUTHOR = 'main_author';
    const SIDEAUTHOR = 'side_author';
    const SUPPORTER  = 'supporter';
    const OPPONENT   = 'opponent';
    const NEUTRAL    = 'neutral';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::EDIT,
            self::MAINAUTHOR,
            self::SIDEAUTHOR,
            self::SUPPORTER,
            self::OPPONENT,
            self::NEUTRAL,
        ));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\Proposal';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $proposal, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($proposal))) {
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

        $isAdmin      = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
        $isMainAuthor = ($proposal->getMainAuthor() === $user);
        $isSideAuthor = $proposal->getSideAuthors()->contains($user);
        $isPublic     = $proposal->isPublic();
        $isSupporter  = $proposal->getSupportiveUsers()->contains($user);
        $isOpponent   = $proposal->getOpposedUsers()->contains($user);
        $isNeutral    = (false === $isSupporter and false === $isOpponent);

        switch ($attribute) {
            case self::EDIT:
                if ($isAdmin or $isMainAuthor or $isSideAuthor or $isPublic) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::MAINAUTHOR:
                if ($isMainAuthor) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::SIDEAUTHOR:
                if ($isSideAuthor) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::SUPPORTER:
                if ($isSupporter) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::OPPONENT:
                if ($isOpponent) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::NEUTRAL:
                if ($isNeutral) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
