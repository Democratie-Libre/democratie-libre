<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Hashes the plain password of a user when it is saved.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class PasswordHashListener
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $this->hash($event->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $this->hash($event->getObject());
    }

    private function hash(object $object): void
    {
        if (!$object instanceof User || null === $object->getPlainPassword()) {
            return;
        }

        $object->setPassword($this->passwordHasher->hashPassword($object, $object->getPlainPassword()));
        $object->setPlainPassword(null);
    }
}
