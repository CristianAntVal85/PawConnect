<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHealthRecordRequest extends FormRequest
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
            'type' => [
                'required',
                Rule::in(['vaccination', 'deworming', 'checkup', 'surgery', 'medication']),
            ],
            'last_date' => ['nullable', 'date'],
            'periodicity_days' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'notifications_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de registro sanitario es obligatorio.',
            'type.in' => 'El tipo de registro debe ser: vaccination, deworming, checkup, surgery o medication.',
            'last_date.date' => 'La fecha debe ser válida.',
            'periodicity_days.min' => 'La periodicidad debe ser mayor a 0 días.',
            'periodicity_days.integer' => 'La periodicidad debe ser un número entero.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Get the body parameters for API documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'type' => [
                'description' => 'Tipo de registro sanitario',
                'example' => 'vaccination',
            ],
            'last_date' => [
                'description' => 'Fecha del último registro (YYYY-MM-DD)',
                'example' => '2026-01-15',
            ],
            'periodicity_days' => [
                'description' => 'Días entre registros (para calcular próxima fecha)',
                'example' => 365,
            ],
            'notes' => [
                'description' => 'Notas adicionales sobre el registro',
                'example' => 'Vacuna antirrábica anual',
            ],
            'notifications_active' => [
                'description' => 'Activar notificaciones para este registro',
                'example' => true,
            ],
        ];
    }
}
