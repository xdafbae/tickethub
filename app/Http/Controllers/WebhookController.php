<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessMidtransWebhook;

class WebhookController extends Controller
{
    public function midtrans(Request $request)
    {
        $payload = $request->all();

        // Pastikan ada minimal field untuk diproses
        if (!isset($payload['order_id'])) {
            return response()->json(['ok' => false, 'error' => 'order_id missing'], 400);
        }

        // Dispatch ke queue untuk diproses async
        ProcessMidtransWebhook::dispatch($payload);

        return response()->json(['ok' => true]);
    }
}