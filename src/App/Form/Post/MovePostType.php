<?php

namespace App\Form\Post;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MovePostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('discussion', 'entity', [
                'class' => 'App:PublicDiscussion',
                'property' => 'title',
                'required' => true,
            ])
            ->add('Confirm', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Post',
        ]);
    }

    public function getName()
    {
        return 'move_post';
    }
}
