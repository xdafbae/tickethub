<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class GateEntryController extends Controller
{
    // Mobile scanner page
    public function scanner()
    {
        return view('gate.scanner');
    }

    // Validate QR content
    public function validateQr(Request $request)
    {
        $qr = (string) $request->input('qr', '');
        if ($qr === '') {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'QR kosong'], 422);
        }

        // Expect JSON { v: 1, d: { order_id, user_id, external_ref, issued_at }, sig: hmac }
        try {
            $parsed = json_decode($qr, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'Format QR tidak valid'], 422);
        }

        $v = $parsed['v'] ?? null;
        $d = $parsed['d'] ?? null;
        $sig = (string) ($parsed['sig'] ?? '');

        if ($v !== 1 || !is_array($d)) {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'Struktur QR tidak dikenal'], 422);
        }

        // Verify signature against payload "d"
        $secret = (string) env('E_TICKET_SECRET', '');
        $payloadJson = json_encode($d, JSON_UNESCAPED_SLASHES);
        $expectedSig = $secret !== '' ? hash_hmac('sha256', (string) $payloadJson, $secret) : '';

        if ($secret === '' || $sig === '' || !hash_equals($expectedSig, $sig)) {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'Signature QR tidak valid'], 422);
        }

        // Validate order
        $orderId = (int) ($d['order_id'] ?? 0);
        $external = $d['external_ref'] ?? null;
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'Order tidak ditemukan'], 404);
        }

        // Cross-check external_ref
        if ($external && $order->external_ref && $order->external_ref !== $external) {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'Order tidak cocok'], 422);
        }

        // Determine refund from latest payment status (if any)
        $latestPayment = $order->payments()->latest()->first();
        $isRefunded = in_array($latestPayment?->status, ['refund', 'refunded', 'chargeback'], true)
            || $order->status === 'refunded';

        if ($isRefunded) {
            return response()->json([
                'ok' => true,
                'status' => 'refunded',
                'order_id' => $order->id,
                'event' => $order->event?->title,
                'buyer' => $order->buyer_name,
            ], 200);
        }

        if ($order->status !== 'paid') {
            return response()->json(['ok' => false, 'status' => 'invalid', 'error' => 'Order belum dibayar'], 422);
        }

        // Already used?
        if ($order->checkin_status === 'used') {
            return response()->json([
                'ok' => true,
                'status' => 'used',
                'order_id' => $order->id,
                'event' => $order->event?->title,
                'buyer' => $order->buyer_name,
                'checked_in_at' => optional($order->checked_in_at)->toIso8601String(),
            ], 200);
        }

        // Mark used
        $order->checkin_status = 'used';
        $order->checked_in_at = now();
        $order->save();

        return response()->json([
            'ok' => true,
            'status' => 'valid',
            'order_id' => $order->id,
            'event' => $order->event?->title,
            'buyer' => $order->buyer_name,
            'checked_in_at' => $order->checked_in_at->toIso8601String(),
        ], 200);
    }
}