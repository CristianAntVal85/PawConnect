<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:9999'],
            'min_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'recommended_breeds' => ['nullable', 'array'],
            'recommended_breeds.*' => ['string', 'max:100'],
            'photo' => ['nullable', 'string', 'url'],
        ];
    }

    /**
     * Descripción de los parámetros del cuerpo de la petición para Scribe.
     */
    public function bodyParameters(): array
    {
        return [
            'provider_id' => [
                'description' => 'ID del proveedor del producto.',
                'example' => 1,
            ],
            'name' => [
                'description' => 'Nombre del producto.',
                'example' => 'Alimento Premium para Perros',
            ],
            'category' => [
                'description' => 'Categoría del producto (e.g., comida, juguete, accesorario).',
                'example' => 'comida',
            ],
            'description' => [
                'description' => 'Descripción detallada del producto.',
                'example' => 'Alimento seco de alta calidad con ingredientes naturales',
            ],
            'price' => [
                'description' => 'Precio del producto en EUR.',
                'example' => 29.99,
            ],
            'stock' => [
                'description' => 'Cantidad disponible en stock.',
                'example' => 50,
            ],
            'min_weight_kg' => [
                'description' => 'Peso mínimo de la mascota recomendado (opcional).',
                'example' => 10,
            ],
            'max_weight_kg' => [
                'description' => 'Peso máximo de la mascota recomendado (opcional).',
                'example' => 30,
            ],
            'recommended_breeds' => [
                'description' => 'Array de razas recomendadas (opcional).',
                'example' => ['Labrador', 'Golden Retriever'],
            ],
            'photo' => [
                'description' => 'URL de la foto del producto.',
                'example' => 'https://example.com/product.jpg',
            ],
        ];
    }
}
