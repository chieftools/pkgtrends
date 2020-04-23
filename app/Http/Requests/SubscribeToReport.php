<?php

namespace IronGate\Pkgtrends\Http\Requests;

use IronGate\Pkgtrends\Rules\Captcha;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeToReport extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'              => 'required|max:255|email',
            'h-captcha-response' => ['required', new Captcha],
        ];
    }
}
