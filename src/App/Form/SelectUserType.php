<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use App\Entity\PrivateDiscussion;

class SelectUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $membersIds = $options['membersIds'];

        $builder
            ->add('user', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => 'username',
                'query_builder' => function (EntityRepository $er) use ($membersIds) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:membersIds)')
                        ->setParameter('membersIds', $membersIds)
                    ;

                    return $qb;
                },
                'required'      => true,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'membersIds'  => null,
        ]);
    }
}
