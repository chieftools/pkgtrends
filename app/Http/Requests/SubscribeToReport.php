<?php

namespace ChiefTools\Pkgtrends\Http\Requests;

use ChiefTools\Pkgtrends\Rules\Captcha;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeToReport extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'                 => 'required|max:255|email',
            'cf-turnstile-response' => ['required', new Captcha],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl() . '#subscriptionForm';
    }
}
