<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class EditThemeType extends AbstractType
{
    private $descendantsId;

    public function __construct($descendantsId)
    {
        $this->descendantsId = $descendantsId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $descendantsId = $this->descendantsId;

        $builder
            ->add('title', 'text')
            ->add('description', 'textarea')
            ->add('parent', 'entity', [
                'class' => 'App:Theme',
                'property' => 'title',
                // selects all the themes exept the one considered and all its descendants
                'query_builder' => function (EntityRepository $er) use ($descendantsId) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:descendantsId)')
                        ->setParameter('descendantsId', $descendantsId)
                    ;

                    return $qb;
                },
                // if the user wants the theme to be a root, he should select a null value for the parent
                'required' => false,
            ])
            ->add('file')
            ->add('enregistrer', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Theme',
        ]);
    }

    public function getName()
    {
        return 'edit_theme';
    }
}
