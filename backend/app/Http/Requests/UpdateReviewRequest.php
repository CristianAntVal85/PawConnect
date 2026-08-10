<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'body'   => ['sometimes', 'string', 'max:1000'],
            'photo'  => ['nullable', 'image', 'max:5120'], // 5MB
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'rating' => [
                'description' => 'Calificación numérica de 1 a 5 estrellas (opcional).',
                'example'     => 5,
            ],
            'body' => [
                'description' => 'Comentario/reseña del lugar (opcional).',
                'example'     => 'Excelente lugar para mascotas.',
            ],
            'photo' => [
                'description' => 'Foto opcional de la reseña.',
                'example'     => null,
            ],
        ];
    }
}
