<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\ProposalVersion;

class ProposalVersionRepository extends EntityRepository
{
    public function findPreviousVersion(ProposalVersion $proposalVersion)
    {
        return $this->findOneBy([
            'recordedProposal' => $proposalVersion->getRecordedProposal(),
            'versionNumber'    => $proposalVersion->getVersionNumber() - 1,
        ]);
    }

    public function findNextVersion(ProposalVersion $proposalVersion)
    {
        return $this->findOneBy([
            'recordedProposal' => $proposalVersion->getRecordedProposal(),
            'versionNumber'    => $proposalVersion->getVersionNumber() + 1,
        ]);
    }
}
