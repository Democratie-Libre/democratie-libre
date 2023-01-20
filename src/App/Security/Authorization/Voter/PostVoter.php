<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Post;
use App\Entity\User;

class PostVoter extends Voter
{
    const SHOW_PRIVATE_DISCUSSION_ICON = 'show_private_discussion_icon';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::SHOW_PRIVATE_DISCUSSION_ICON
        ])) {
            return false;
        }

        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $post = $subject;
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::SHOW_PRIVATE_DISCUSSION_ICON:
                return $this->showPrivateDiscussionIcon($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function showPrivateDiscussionIcon($post, $user)
    {
        return $user !== $post->getAuthor();
    }
}
