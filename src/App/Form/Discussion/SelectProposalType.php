<?php

namespace App\Form\Discussion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SelectProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('proposal', 'entity', [
                'class' => 'App:Proposal',
                'property' => 'title',
                'required' => true,
            ])
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\PublicDiscussion',
        ]);
    }
}
