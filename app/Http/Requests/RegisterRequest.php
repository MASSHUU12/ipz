<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\PhoneNumber;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone_number')) {
            $phone = new PhoneNumber($this->input('phone_number'));
            $this->merge([
                'phone_number' => $phone->formatE164(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        return [
            'email'        => 'required_without:phone_number|email|unique:users,email',
            'phone_number' => 'required_without:email|unique:users,phone_number|phone:INTERNATIONAL',
            'password'     => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:255',
                'regex:/[a-z]/',      // lowercase
                'regex:/[A-Z]/',      // uppercase
                'regex:/[0-9]/',      // number
                'regex:/[@$!%*?&]/',  // special character
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
        ];
    }
}
