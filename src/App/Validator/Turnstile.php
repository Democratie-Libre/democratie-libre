<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class Turnstile extends Constraint
{
    public string $message = 'La vérification anti-robot a échoué. Veuillez réessayer.';
}
