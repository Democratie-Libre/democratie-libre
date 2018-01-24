<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\OldProposal;
use App\Entity\User;

class OldProposalVoter extends Voter
{
    const NOT_FIRST_VERSION = 'not_first_version';
    const LAST_VERSION      = 'last_version';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::NOT_FIRST_VERSION,
            self::LAST_VERSION
        ])) {
            return false;
        }

        if (!$subject instanceof OldProposal) {
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

        $oldProposal = $subject;

        switch ($attribute) {
            case self::NOT_FIRST_VERSION:
                return $this->isNotFirstVersion($oldProposal);
            case self::LAST_VERSION:
                return $this->isLastVersion($oldProposal);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function isNotFirstVersion(OldProposal $oldProposal)
    {
        $oldVersionNumber = $oldProposal->getOldVersionNumber();

        return $oldVersionNumber !== 1;
    }

    private function isLastVersion(OldProposal $oldProposal)
    {
        $oldVersionNumber      = $oldProposal->getOldVersionNumber();
        $proposalVersionNumber = $oldProposal->getRecordedProposal()->getVersionNumber();

        return $oldVersionNumber + 1 === $proposalVersionNumber;
    }
}
