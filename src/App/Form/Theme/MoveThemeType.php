<?php

namespace App\Form\Theme;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Theme;

class MoveThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $descendantsIds = $options['descendantsIds'];

        $builder
            ->add('parent', EntityType::class, [
                'class'             => Theme::class,
                'choice_label'      => 'title',
                // selects all the themes exept the one considered and all its descendants
                'query_builder'     => function (EntityRepository $er) use ($descendantsIds) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:descendantsIds)')
                        ->setParameter('descendantsIds', $descendantsIds)
                    ;

                    return $qb;
                },
                // if the user wants the theme to be a root, he should select a null value for the parent
                'required'          => false,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'     => Theme::class,
            'descendantsIds' => null
        ]);
    }
}
