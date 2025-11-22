<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $orders = Order::with(['event','user'])
            ->when($q !== '', function($query) use ($q) {
                $query->where(function($qq) use ($q) {
                    $qq->where('id', (int) $q)
                       ->orWhere('buyer_name', 'like', "%{$q}%")
                       ->orWhere('buyer_email', 'like', "%{$q}%")
                       ->orWhere('external_ref', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'q'));
    }

    public function show(Order $order)
    {
        $order->load(['event','user','payments']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|string|in:pending,paid,cancelled,refunded',
        ]);

        if ($data['status'] === 'refunded') {
            return $this->refund($request, $order);
        }

        $order->status = $data['status'];
        $order->save();

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', "Status order #{$order->id} diubah ke {$order->status}");
    }

    public function refund(Request $request, Order $order)
    {
        $payload = $request->validate([
            'reason' => 'nullable|string|max:500',
            'note'   => 'nullable|string|max:1000',
        ]);

        DB::transaction(function() use ($order, $payload) {
            // Tandai order sebagai refunded
            $order->status = 'refunded';
            $order->save();

            // Catat Payment refund
            Payment::create([
                'order_id' => $order->id,
                'provider' => 'manual',
                'provider_transaction_id' => 'REFUND-' . $order->id . '-' . now()->format('YmdHis'),
                'status' => 'refund',
                'amount' => $order->total,
                'redirect_url' => null,
                'raw_payload' => [
                    'reason' => $payload['reason'] ?? null,
                    'note'   => $payload['note'] ?? null,
                    'refunded_at' => now()->toIso8601String(),
                ],
            ]);
        });

        // Kirim email notifikasi ke user
        try {
            Mail::send('emails.refund_notification', [
                'order' => $order->fresh(['event']),
                'reason' => $payload['reason'] ?? null,
                'note' => $payload['note'] ?? null,
            ], function($m) use ($order) {
                $m->to($order->buyer_email, $order->buyer_name)
                  ->subject('Pengembalian Dana Order #' . $order->id);
            });
        } catch (\Throwable $e) {
            // Biarkan refund tetap sukses, hanya beri tahu admin soal email gagal
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', "Order #{$order->id} berhasil di-refund. Email gagal dikirim: " . $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', "Order #{$order->id} berhasil di-refund dan notifikasi dikirim.");
    }
}