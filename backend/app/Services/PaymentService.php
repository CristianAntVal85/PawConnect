<?php

namespace App\Services;

use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crea un PaymentIntent en Stripe.
     *
     * @param  int    $amountCents  Importe en céntimos (p.ej. 1999 = $19.99)
     * @param  string $currency     Código ISO de moneda (default: usd)
     * @param  array  $metadata     Metadatos opcionales para el intent
     *
     * @throws ApiErrorException
     */
    public function createPaymentIntent(int $amountCents, string $currency = 'usd', array $metadata = []): PaymentIntentData
    {
        $intent = PaymentIntent::create([
            'amount'               => $amountCents,
            'currency'             => $currency,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata'             => $metadata,
        ]);

        return new PaymentIntentData(
            id:           $intent->id,
            clientSecret: $intent->client_secret,
        );
    }

    /**
     * Recupera un PaymentIntent de Stripe.
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Verifica que un PaymentIntent tiene estado "succeeded".
     */
    public function isPaymentSucceeded(string $paymentIntentId): bool
    {
        try {
            $intent = $this->retrievePaymentIntent($paymentIntentId);
            return $intent->status === 'succeeded';
        } catch (ApiErrorException) {
            return false;
        }
    }

    /**
     * Convierte un importe decimal a céntimos enteros para Stripe.
     *
     * @param  float $amount  Importe en unidades (p.ej. 19.99)
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
