<?php

namespace App\Form\Discussion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ChangeAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('admin', 'entity', [
                'class'    => 'App:User',
                'property' => 'username',
                'required' => true,
            ])
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\PrivateDiscussion',
        ]);
    }

    public function getName()
    {
        return 'private_discussion_change_admin';
    }
}
