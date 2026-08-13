<?php

namespace App\Exports;

use App\Models\PlaceReview;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReviewsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return PlaceReview::with('place', 'user')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Lugar',
            'Reseñador',
            'Calificación',
            'Comentario',
            'Estado',
            'Motivo del rechazo',
            'Creado',
        ];
    }

    public function map($review): array
    {
        return [
            $review->id,
            $review->place->name ?? 'N/A',
            $review->user->name ?? 'N/A',
            $review->rating . '/5',
            substr($review->body ?? '', 0, 50) . (strlen($review->body ?? '') > 50 ? '...' : ''),
            ucfirst($review->status),
            $review->rejection_reason ?? '-',
            $review->created_at->format('d/m/Y H:i'),
        ];
    }
}
