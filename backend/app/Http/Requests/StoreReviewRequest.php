<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body'   => ['required', 'string', 'max:1000'],
            'photo'  => ['nullable', 'image', 'max:5120'], // 5MB
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'rating' => [
                'description' => 'Calificación numérica de 1 a 5 estrellas.',
                'example'     => 5,
            ],
            'body' => [
                'description' => 'Comentario/reseña del lugar.',
                'example'     => 'Un lugar excelente para llevar perros. Muy limpio y con muchos espacios verdes.',
            ],
            'photo' => [
                'description' => 'Foto opcional de la reseña.',
                'example'     => null,
            ],
        ];
    }
}
