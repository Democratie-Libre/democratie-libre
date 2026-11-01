<?php

namespace App\Form\Type;

use App\Validator\Turnstile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TurnstileType extends AbstractType
{
    public function __construct(
        #[Autowire('%env(TURNSTILE_SITE_KEY)%')]
        private string $siteKey,
        #[Autowire('%turnstile_enabled%')]
        private bool $enabled,
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['site_key'] = $this->siteKey;
        $view->vars['enabled'] = $this->enabled;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped'      => false,
            'required'    => false,
            'constraints' => [new Turnstile()],
        ]);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
