<?php

namespace IronGate\Pkgtrends\Rules;

use Illuminate\Contracts\Validation\Rule;

class Captcha implements Rule
{
    public function passes($attribute, $value)
    {
        return rescue(function () use ($value) {
            $response = http('https://hcaptcha.com/')->post('siteverify', [
                'query' => [
                    'secret'   => config('services.hcaptcha.secret'),
                    'response' => $value,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true)['success'] ?? false;
        }, false);
    }

    public function message(): string
    {
        return 'You kinda look like a bot, or hCaptcha is broken. Please try again.';
    }
}
