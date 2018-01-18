<?php

namespace App\Form\Discussion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class AddPrivateDiscussionType extends AbstractType
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $this->userId;

        $builder
            ->add('title', 'text')
            ->add('members', 'entity', [
                'class' => 'App\Entity\User',
                'property' => 'username',
                'query_builder' => function (EntityRepository $er) use ($userId) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:userId)')
                        ->setParameter('userId', $userId)
                    ;

                    return $qb;
                },
                'required' => true,
                'expanded' => true,
                'multiple' => true,
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
        return 'add_private_discussion';
    }
}
