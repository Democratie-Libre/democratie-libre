<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProposalDraftVoter implements VoterInterface
{
    const VIEW       = 'view';
    const EDIT       = 'edit';
    const PUBLISH    = 'publish';
    const DELETE     = 'delete';
    const MAINAUTHOR = 'main_author';
    const SIDEAUTHOR = 'side_author';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::VIEW,
            self::EDIT,
            self::PUBLISH,
            self::DELETE,
            self::MAINAUTHOR,
            self::SIDEAUTHOR,
        ));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\ProposalDraft';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $proposalDraft, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($proposalDraft))) {
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

        $isAdmin      = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
        $isMainAuthor = ($proposalDraft->getMainAuthor() === $user);
        $isSideAuthor = $proposalDraft->getSideAuthors()->contains($user);

        switch ($attribute) {
            case self::VIEW:
                if ($isAdmin or $isMainAuthor or $isSideAuthor) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::EDIT:
                if ($isAdmin or $isMainAuthor or $isSideAuthor) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::PUBLISH:
                if ($isAdmin or $isMainAuthor) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::DELETE:
                if ($isAdmin or $isMainAuthor) {
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
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
