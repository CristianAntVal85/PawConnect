<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'category'    => ['sometimes', 'string', Rule::in('pipican', 'beach', 'park', 'vet', 'shop', 'restaurant', 'hotel')],
            'description' => ['sometimes', 'string', 'max:1000'],
            'lat'         => ['sometimes', 'numeric', 'between:-90,90'],
            'lng'         => ['sometimes', 'numeric', 'between:-180,180'],
            'address'     => ['sometimes', 'string', 'max:500'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'website'     => ['nullable', 'url', 'max:255'],
            'photo'       => ['nullable', 'image', 'max:5120'],
        ];
    }
}
