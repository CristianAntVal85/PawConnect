<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'lat'        => ['required', 'numeric', 'between:-90,90'],
            'lng'        => ['required', 'numeric', 'between:-180,180'],
            'is_default' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lat.between' => 'La latitud debe estar entre -90 y 90.',
            'lng.between' => 'La longitud debe estar entre -180 y 180.',
        ];
    }
}
