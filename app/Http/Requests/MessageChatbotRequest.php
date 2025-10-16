<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageChatbotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation.
     *
     * Normalize common alternative keys (e.g. "tz") into "timezone".
     */
    protected function prepareForValidation(): void
    {
        if ($this->has("tz") && !$this->has("timezone")) {
            $this->merge(["timezone" => $this->input("tz")]);
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
            "content" => "required|string|max:255|min:1",
            "timezone" => "nullable|string|timezone",
        ];
    }
}
