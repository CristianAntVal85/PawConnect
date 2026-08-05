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
            'photos' => ['sometimes', 'array', 'max:6'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * Descripción de los parámetros del cuerpo de la petición para Scribe.
     */
    public function bodyParameters(): array
    {
        return [
            'species' => [
                'description' => 'Especie de la mascota. Todos los campos son opcionales en actualización.',
                'example' => 'dog',
            ],
            'name' => [
                'description' => 'Nombre de la mascota',
                'example' => 'Max',
            ],
            'breed' => [
                'description' => 'Raza de la mascota',
                'example' => 'Golden Retriever',
            ],
            'age' => [
                'description' => 'Edad de la mascota en años',
                'example' => 4,
            ],
            'weight' => [
                'description' => 'Peso de la mascota en kilogramos',
                'example' => 28.0,
            ],
            'size' => [
                'description' => 'Tamaño: small, medium, large, giant',
                'example' => 'large',
            ],
            'energy_level' => [
                'description' => 'Nivel de energía: low, moderate, high, very_high',
                'example' => 'moderate',
            ],
            'personality' => [
                'description' => 'Descripción de la personalidad',
                'example' => 'Tranquilo y cariñoso con niños.',
            ],
            'available_for_walks' => [
                'description' => 'Disponibilidad para paseos sociales',
                'example' => false,
            ],
            'photos' => [
                'description' => 'Array de nuevas imágenes (máximo 6, 4MB cada una)',
                'example' => 'No documentable (tipo file)',
            ],
        ];
    }
}
