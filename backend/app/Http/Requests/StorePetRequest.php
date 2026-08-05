<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePetRequest extends FormRequest
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'species' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:120'],
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
                'description' => 'Especie de la mascota. Ejemplos: dog, cat, rabbit, bird, fish, etc.',
                'example' => 'dog',
            ],
            'name' => [
                'description' => 'Nombre de la mascota',
                'example' => 'Max',
            ],
            'breed' => [
                'description' => 'Raza de la mascota (opcional). Ejemplos: Labrador, Persa, Husky.',
                'example' => 'Labrador Retriever',
            ],
            'age' => [
                'description' => 'Edad de la mascota en años (0-35)',
                'example' => 3,
            ],
            'weight' => [
                'description' => 'Peso de la mascota en kilogramos',
                'example' => 25.5,
            ],
            'size' => [
                'description' => 'Tamaño de la mascota. Valores: small, medium, large, giant',
                'example' => 'medium',
            ],
            'energy_level' => [
                'description' => 'Nivel de energía de la mascota. Valores: low, moderate, high, very_high',
                'example' => 'high',
            ],
            'personality' => [
                'description' => 'Descripción libre de la personalidad y comportamiento de la mascota',
                'example' => 'Muy sociable, le encanta jugar con otros perros y es muy obediente.',
            ],
            'available_for_walks' => [
                'description' => 'Indica si la mascota está disponible para participar en paseos sociales (solo caninos)',
                'example' => true,
            ],
            'photos' => [
                'description' => 'Array de imágenes de la mascota (máximo 6). Formatos: jpg, jpeg, png, webp. Tamaño máximo por imagen: 4MB',
                'example' => 'No documentable (tipo file)',
            ],
        ];
    }
}
