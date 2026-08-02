<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
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
            'species' => ['sometimes', 'required', 'string', 'max:50'],
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'integer', 'min:0', 'max:35'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'size' => ['nullable', 'string', 'max:30'],
            'energy_level' => ['nullable', 'string', 'max:30'],
            'personality' => ['nullable', 'string', 'max:2000'],
            'available_for_walks' => ['sometimes', 'boolean'],
        ];
    }
}
