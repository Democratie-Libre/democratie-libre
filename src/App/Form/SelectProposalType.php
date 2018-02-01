<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Proposal;
use App\Entity\PublicDiscussion;

class SelectProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('proposal', EntityType::class, [
                'class'        => Proposal::class,
                'choice_label' => 'title',
                'required'     => true,
            ])
            ->add('save', SubmitType::class)
        ;
    }
}
