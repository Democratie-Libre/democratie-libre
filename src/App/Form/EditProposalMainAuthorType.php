<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use App\Entity\User;

class EditProposalMainAuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('abstract', 'textarea')
            ->add('content', 'textarea')
            ->add('file')
            ->add('mainAuthor', 'entity', [
                'class' => 'App:User',
                'property' => 'username',
                'required' => false,
            ])
            ->add('sideAuthors', 'entity', [
                'class' => 'App\Entity\User',
                'property' => 'username',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('enregistrer', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'App\Entity\Proposal',
        ]);
    }

    public function getName()
    {
        return 'edit_proposal_mainauthor';
    }
}
