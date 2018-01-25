<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class SelectUserType extends AbstractType
{
    private $usersIds;

    public function __construct($usersIds)
    {
        $this->usersIds = $usersIds;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usersIds = $this->usersIds;

        $builder
            ->add('user', 'entity', [
                'class'         => 'App:User',
                'property'      => 'username',
                'query_builder' => function (EntityRepository $er) use ($usersIds) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:usersIds)')
                        ->setParameter('usersIds', $usersIds)
                    ;

                    return $qb;
                },
                'required'      => true,
            ])
            ->add('save', 'submit')
        ;
    }
}
