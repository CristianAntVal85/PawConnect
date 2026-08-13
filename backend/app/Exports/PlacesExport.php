<?php

namespace App\Exports;

use App\Models\PetFriendlyPlace;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlacesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return PetFriendlyPlace::with('creator')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Categoría',
            'Estado',
            'Dirección',
            'Teléfono',
            'Sitio Web',
            'Sugerido por',
            'Calificación promedio',
            'Total de reseñas',
            'Creado',
        ];
    }

    public function map($place): array
    {
        $reviews = $place->reviews()->where('status', 'approved')->get();
        $avgRating = $reviews->count() > 0 ? number_format($reviews->avg('rating'), 1) : '-';

        return [
            $place->id,
            $place->name,
            $place->category,
            ucfirst($place->status),
            $place->address,
            $place->phone ?? '-',
            $place->website ?? '-',
            $place->creator->name ?? 'N/A',
            $avgRating,
            $place->reviews()->where('status', 'approved')->count(),
            $place->created_at->format('d/m/Y'),
        ];
    }
}
