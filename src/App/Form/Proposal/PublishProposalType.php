<?php

namespace App\Form\Proposal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Proposal;

class PublishProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', 'entity', [
                'class'    => 'App:Theme',
                'property' => 'title',
                'required' => true,
            ])
            ->add('publish', 'submit')
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
        return 'publish_proposal';
    }
}
