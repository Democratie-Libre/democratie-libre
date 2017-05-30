<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByRole($role)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%')
        ;

        return $qb->getQuery()->getResult();
    }

    public function count()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(u)')
            ->from($this->_entityName, 'u')
        ;

        return intval($qb->getQuery()->getSingleScalarResult());
    }
}
