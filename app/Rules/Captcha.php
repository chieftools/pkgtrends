<?php

namespace ChiefTools\Pkgtrends\Rules;

use Illuminate\Contracts\Validation\Rule;

class Captcha implements Rule
{
    public function passes($attribute, $value)
    {
        return rescue(function () use ($value) {
            $response = http('https://challenges.cloudflare.com/')->post('turnstile/v0/siteverify', [
                'form_params' => [
                    'secret'   => config('services.turnstile.secret'),
                    'response' => $value,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true)['success'] ?? false;
        }, false);
    }

    public function message(): string
    {
        return 'You kinda look like a bot, or Cloudflare Turnstile is broken. Please try again.';
    }
}
