<?php

namespace IronGate\Pkgtrends\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IronGate\Pkgtrends\Rules\Recaptcha;

class SubscribeToReport extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'                => 'required|max:255|email',
            'g-recaptcha-response' => ['required', new Recaptcha()],
        ];
    }
}
