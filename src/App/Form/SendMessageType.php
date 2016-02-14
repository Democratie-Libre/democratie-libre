<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use App\Entity\User;

class SendMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('addressees', 'entity', [
                'class' => 'App:User',
                'property' => 'username',
                'required' => true,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('content', 'textarea')
            ->add('envoyer', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'App\Entity\Message',
            ])
        ;
    }

    public function getName()
    {
        return 'send_message';
    }
}
