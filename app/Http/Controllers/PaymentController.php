<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        try {
            // Configurar tu clave secreta de Stripe (desde .env)
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Recibir el monto desde la app (en centavos)
            $amount = $request->input('amount', 1000); // valor por defecto: $10.00

            // Crear un PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd', // o la moneda que uses
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
