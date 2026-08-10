<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'category'    => ['required', 'string', Rule::in('pipican', 'beach', 'park', 'vet', 'shop', 'restaurant', 'hotel')],
            'description' => ['required', 'string', 'max:1000'],
            'lat'         => ['required', 'numeric', 'between:-90,90'],
            'lng'         => ['required', 'numeric', 'between:-180,180'],
            'address'     => ['required', 'string', 'max:500'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'website'     => ['nullable', 'url', 'max:255'],
            'photo'       => ['nullable', 'image', 'max:5120'], // 5MB
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name'        => ['example' => 'Parque Central'],
            'category'    => ['example' => 'park', 'description' => 'Categoría: pipican, beach, park, vet, shop, restaurant, hotel'],
            'description' => ['example' => 'Hermoso parque con áreas verdes y fuentes'],
            'lat'         => ['example' => -33.9249],
            'lng'         => ['example' => -18.4241],
            'address'     => ['example' => 'Avenida Principal 123, Ciudad'],
            'phone'       => ['example' => '+34 911 234 567'],
            'website'     => ['example' => 'https://example.com'],
        ];
    }
}
