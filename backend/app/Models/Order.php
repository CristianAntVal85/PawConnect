<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'pet_id',
        'quantity',
        'total_price',
        'shipping_address',
        'payment_method',
        'stripe_payment_id',
        'tracking_number',
        'tracking_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Generar número de tracking único
     *
     * @return string
     */
    public static function generateTrackingNumber(): string
    {
        return 'PW' . strtoupper(uniqid());
    }

    /**
     * Verificar si el pedido puede recibir tracking
     *
     * @return bool
     */
    public function canHaveTracking(): bool
    {
        return in_array($this->status, ['confirmed', 'shipped', 'delivered']);
    }
}
