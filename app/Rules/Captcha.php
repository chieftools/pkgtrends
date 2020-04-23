<?php

namespace IronGate\Pkgtrends\Rules;

use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Rule;

class Captcha implements Rule
{
    public function passes($attribute, $value)
    {
        return rescue(function () use ($value) {
            $http = new Client(['base_uri' => 'https://hcaptcha.com/']);

            $response = $http->post('siteverify', [
                'query' => [
                    'secret'   => config('services.hcaptcha.secret'),
                    'response' => $value,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true)['success'] ?? false;
        }, false);
    }

    public function message()
    {
        return 'You kinda look like a bot, or hCaptcha is broken. Please try again.';
    }
}
