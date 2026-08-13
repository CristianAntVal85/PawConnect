<?php

namespace App\Exports;

use App\Models\Pet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PetsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Pet::with('user')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Especie',
            'Raza',
            'Edad (años)',
            'Peso (kg)',
            'Tamaño',
            'Energía',
            'Disponible para paseos',
            'Dueño',
            'Registrado',
        ];
    }

    public function map($pet): array
    {
        return [
            $pet->id,
            $pet->name,
            $pet->species,
            $pet->breed,
            $pet->age,
            $pet->weight,
            $pet->size,
            $pet->energy_level,
            $pet->available_for_walks ? 'Sí' : 'No',
            $pet->user->name ?? 'N/A',
            $pet->created_at->format('d/m/Y H:i'),
        ];
    }
}
