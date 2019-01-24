<?php

namespace IronGate\Pkgtrends\Rules;

use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Rule;

class Recaptcha implements Rule
{
    public function passes($attribute, $value)
    {
        return rescue(function () use ($value) {
            $http = new Client(['base_uri' => 'https://www.google.com/recaptcha/api/']);

            $response = $http->post('siteverify', [
                'query' => [
                    'secret'   => config('services.recaptcha.secret'),
                    'response' => $value,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true)['success'] ?? false;
        }, false);
    }

    public function message()
    {
        return "We think you're a bot, or Google is broken. Please try again.";
    }
}
