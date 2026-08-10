<?php

namespace App\Services;

/**
 * Resultado de crear un PaymentIntent, desacoplado del SDK de Stripe.
 */
class PaymentIntentData
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientSecret,
    ) {
    }
}
