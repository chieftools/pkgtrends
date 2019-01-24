<?php

namespace IronGate\Pkgtrends\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeToReport extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|max:255|email',
        ];
    }
}
