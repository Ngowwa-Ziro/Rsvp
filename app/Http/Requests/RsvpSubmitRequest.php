<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RsvpSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the controller checks the invitation stored in the session
    }

    public function rules(): array
    {
        return [
            'attending' => ['required', 'boolean'],
            'guests' => ['nullable', 'array', 'max:20'],
            'guests.*' => ['nullable', 'string', 'max:100'],
            'dietary' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'website' => ['prohibited'], // honeypot: real people leave it empty
        ];
    }

    public function messages(): array
    {
        return [
            'attending.required' => 'Please tell us whether you will attend.',
            'website.prohibited' => 'Submission rejected.',
            'guests.*.max' => 'Each name must be 100 characters or fewer.',
        ];
    }
}
