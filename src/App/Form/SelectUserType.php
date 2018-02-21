<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;

class SelectUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $unlisted_users = $options['unlisted_users'];

        $builder
            ->add('user', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => 'username',
                'query_builder' => function (EntityRepository $er) use ($unlisted_users) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:unlisted_users)')
                        ->setParameter('unlisted_users', $unlisted_users)
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
            'unlisted_users'  => null,
        ]);
    }
}
