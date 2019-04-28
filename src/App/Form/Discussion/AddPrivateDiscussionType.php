<?php

namespace App\Form\Discussion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use App\Entity\PrivateDiscussion;
use App\Entity\User;

class AddPrivateDiscussionType extends AbstractType
{
    private $userId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->userId = $options['userId'];
        $userId = $this->userId;

        $builder
            ->add('title', TextType::class)
            ->add('members', EntityType::class, [
                'class'         => User::class,
                'attr'          => [
                    'class' => 'multi-select'
                ],
                'choice_label'  => 'username',
                'query_builder' => function (EntityRepository $er) use ($userId) {
                    $qb = $er->createQueryBuilder('u');
                    $qb
                        ->where('u.id NOT IN(:userId)')
                        ->setParameter('userId', $userId)
                    ;

                    return $qb;
                },
                'required'      => true,
                'expanded'      => false,
                'multiple'      => true,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrivateDiscussion::class,
            'userId'     => null,
        ]);
    }
}
