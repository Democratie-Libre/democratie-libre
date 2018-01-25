<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\OldProposal;

class OldProposalRepository extends EntityRepository
{
    public function findPreviousVersion(OldProposal $oldProposal)
    {
        return $this->findOneBy([
            'recordedProposal' => $oldProposal->getRecordedProposal(),
            'oldVersionNumber' => $oldProposal->getOldVersionNumber() - 1,
        ]);
    }

    public function findNextVersion(OldProposal $oldProposal)
    {
        return $this->findOneBy([
            'recordedProposal' => $oldProposal->getRecordedProposal(),
            'oldVersionNumber' => $oldProposal->getOldVersionNumber() + 1,
        ]);
    }
}
