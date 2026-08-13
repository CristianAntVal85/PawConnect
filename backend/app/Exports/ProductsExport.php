<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('provider')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Categoría',
            'Proveedor',
            'Precio',
            'Stock',
            'Peso mín (kg)',
            'Peso máx (kg)',
            'Razas recomendadas',
            'Creado',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category,
            $product->provider->name ?? 'N/A',
            '$' . number_format($product->price, 2),
            $product->stock,
            $product->min_weight_kg ?? '-',
            $product->max_weight_kg ?? '-',
            $product->recommended_breeds ?? '-',
            $product->created_at->format('d/m/Y'),
        ];
    }
}
