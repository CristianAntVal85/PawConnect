<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWebPetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'species' => ['required', 'string', Rule::in(['dog', 'cat', 'other'])],
            'name' => ['required', 'string', 'max:120'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'integer', 'min:0', 'max:35'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'size' => ['required', 'string', Rule::in(['small', 'medium', 'large'])],
            'energy_level' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
            'personality' => ['nullable', 'string', 'max:2000'],
            'available_for_walks' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
