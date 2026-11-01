<?php

namespace App\Validator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TurnstileValidator extends ConstraintValidator
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private RequestStack $requestStack,
        #[Autowire('%env(TURNSTILE_SECRET)%')]
        private string $secret,
        #[Autowire('%turnstile_enabled%')]
        private bool $enabled,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$this->enabled) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $token = $request?->request->get('cf-turnstile-response');

        if (!is_string($token) || '' === $token || !$this->verify($token, $request->getClientIp())) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function verify(string $token, ?string $ip): bool
    {
        $body = http_build_query(array_filter([
            'secret'   => $this->secret,
            'response' => $token,
            'remoteip' => $ip,
        ]));

        $context = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $body,
            'timeout' => 5,
        ]]);

        $response = @file_get_contents(self::VERIFY_URL, false, $context);
        if (false === $response) {
            return false;
        }

        $data = json_decode($response, true);

        return is_array($data) && true === ($data['success'] ?? false);
    }
}
