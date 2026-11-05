<?php

namespace App\Http\Requests;

use App\Enums\GuestCategory;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // routes are behind the auth middleware
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone')]);
    }

    public function rules(): array
    {
        $id = $this->route('invitation')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^\+254[17]\d{8}$/', Rule::unique('invitations', 'phone')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150'],
            'category' => ['required', Rule::enum(GuestCategory::class)],
            'max_attendees' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Kenyan mobile number, e.g. 0712 345 678.',
            'phone.unique' => 'Someone with this phone number is already invited.',
        ];
    }
}
