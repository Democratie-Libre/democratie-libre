<?php

namespace App\Form\Proposal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Proposal;

class EditProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file')
            ->add('title', 'text')
            ->add('abstract', 'textarea')
            ->add('argumentation', 'textarea')
            ->add('executionProcedure', 'textarea')
            ->add('save', 'submit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Proposal::class,
        ]);
    }

    public function getName()
    {
        return 'edit_proposal';
    }
}
