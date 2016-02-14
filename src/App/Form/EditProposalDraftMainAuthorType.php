<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProposalDraftMainAuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('abstract', 'textarea')
            ->add('content', 'textarea')
            ->add('mainAuthor', 'entity', [
                'class'    => 'App:User',
                'property' => 'username',
                'required' => false,
            ])
            ->add('sideAuthors', 'entity', [
                'class'    => 'App:User',
                'property' => 'username',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('enregistrer', 'submit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'App\Entity\ProposalDraft',
        ]);
    }

    public function getName()
    {
        return 'edit_proposaldraft_admin';
    }
}
