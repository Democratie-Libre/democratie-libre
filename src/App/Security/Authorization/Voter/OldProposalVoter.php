<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

class OldProposalVoter extends AbstractVoter
{
    const NOT_FIRST_VERSION = 'not_first_version';
    const LAST_VERSION      = 'last_version';

    protected function getSupportedAttributes()
    {
        return array(
            self::NOT_FIRST_VERSION,
            self::LAST_VERSION,
        );
    }

    protected function getSupportedClasses()
    {
        return array('App\Entity\OldProposal');
    }

    protected function isGranted($attribute, $oldProposal, $user = null)
    {
        $oldVersionNumber      = $oldProposal->getOldVersionNumber();
        $proposalVersionNumber = $oldProposal->getRecordedProposal()->getVersionNumber();

        switch ($attribute) {
            case self::NOT_FIRST_VERSION:
                if ($oldVersionNumber !== 1) {
                    return true;
                }
                break;

            case self::LAST_VERSION:
                if ($oldVersionNumber + 1 === $proposalVersionNumber) {
                    return true;
                }
                break;
        }

        return false;
    }
}
