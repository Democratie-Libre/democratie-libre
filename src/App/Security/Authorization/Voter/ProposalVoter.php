<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\User;

class ProposalVoter extends AbstractVoter
{
    const EDIT       = 'edit';
    const AUTHOR     = 'author';
    const SUPPORTER  = 'supporter';
    const OPPONENT   = 'opponent';
    const NEUTRAL    = 'neutral';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getSupportedAttributes()
    {
        return array(
            self::EDIT,
            self::AUTHOR,
            self::SUPPORTER,
            self::OPPONENT,
            self::NEUTRAL,
        );
    }

    public function getSupportedClasses()
    {
        return array('App\Entity\Proposal');
    }

    public function isGranted($attribute, $proposal, $user = null)
    {
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return false;
        }

        // double-check that the User object is the expected entity (this
        // only happens when you did not configure the security system properly)
        if (!$user instanceof User) {
            throw new \LogicException('The user is somehow not our User class!');
        }

        $isAdmin      = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
        $isAuthor     = ($proposal->getAuthor() === $user);
        $isAWiki      = $proposal->isAWiki();
        $isSupporter  = $proposal->getSupporters()->contains($user);
        $isOpponent   = $proposal->getOpponents()->contains($user);
        $isNeutral    = (false === $isSupporter and false === $isOpponent);

        switch ($attribute) {
            case self::EDIT:
                if ($isAdmin or $isAuthor or $isAWiki) {
                    return true;
                }
                break;
            case self::AUTHOR:
                if ($isAuthor) {
                    return true;
                }
                break;
            case self::SUPPORTER:
                if ($isSupporter) {
                    return true;
                }
                break;
            case self::OPPONENT:
                if ($isOpponent) {
                    return true;
                }
                break;
            case self::NEUTRAL:
                if ($isNeutral) {
                    return true;
                }
                break;
        }

        return false;
    }
}
