<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAirQualityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lat' => 'required|numeric|between:49.0,55.0', // Poland
            'lon' => 'required|numeric|between:14.0,24.0',
        ];
    }
    /**
     * @return array<int,float>
     */
    public function coordinates(): array
    {
        // Round here once for cache‐key consistency
        return [
            round($this->input('lat'), 3),
            round($this->input('lon'), 3),
        ];
    }
}
