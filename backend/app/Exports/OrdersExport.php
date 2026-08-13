<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Order::with('user', 'product', 'pet')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Producto',
            'Mascota',
            'Cantidad',
            'Total',
            'Estado',
            'Método de Pago',
            'Dirección de envío',
            'Creado',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->user->name ?? 'N/A',
            $order->product->name ?? 'N/A',
            $order->pet->name ?? 'N/A',
            $order->quantity,
            '$' . number_format($order->total_price, 2),
            ucfirst($order->status),
            $order->payment_method,
            $order->shipping_address,
            $order->created_at->format('d/m/Y H:i'),
        ];
    }
}
