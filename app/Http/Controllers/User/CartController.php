<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SeatMap;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Promo;
use App\Models\PromoUsage;

class CartController extends Controller
{
    private function cartKey(Event $event): string
    {
        return 'cart:event:' . $event->id;
    }

    public function cart(Event $event, Request $request)
    {
        $ticketTypes = TicketType::where('event_id', $event->id)->where('is_active', true)->orderBy('price')->get();

        $cart = $request->session()->get($this->cartKey($event), [
            'buyer' => ['name' => '', 'email' => '', 'phone' => ''],
            'items' => [],
            'seats' => [],
        ]);

        [$mySeats, $seatNamesById, $seatLocks] = $this->getMyLockedSeats($event, $request);

        if (empty($cart['seats'])) {
            $cart['seats'] = $mySeats;
        }

        $total = $this->calcTotal($ticketTypes, $cart['items']);

        return view('user.cart', compact('event', 'ticketTypes', 'cart', 'total', 'seatNamesById', 'seatLocks'));
    }

    public function update(Event $event, Request $request)
    {
        $validated = $request->validate([
            'buyer.name' => 'required|string|max:120',
            'buyer.email' => 'required|email',
            'buyer.phone' => 'nullable|string|max:50',
            'items' => 'array',
            'items.*' => 'nullable|integer|min:0',
            'seats' => 'array',
            'seats.*' => 'string',
        ]);

        $ticketTypes = TicketType::where('event_id', $event->id)->where('is_active', true)->get();
        $items = [];
        foreach ($ticketTypes as $tt) {
            $qty = (int)($validated['items'][$tt->id] ?? 0);
            if ($tt->quota !== null) {
                $qty = min($qty, (int)$tt->quota);
            }
            if ($qty > 0) { $items[$tt->id] = $qty; }
        }

        $cart = [
            'buyer' => $validated['buyer'],
            'items' => $items,
            'seats' => array_values($validated['seats'] ?? []),
        ];

        $request->session()->put($this->cartKey($event), $cart);

        return redirect()->route('user.checkout.show', ['event' => $event->id])
            ->with('status', 'Cart diperbarui. Lanjut ke checkout.');
    }

    public function checkout(Event $event, Request $request)
    {
        $ticketTypes = TicketType::where('event_id', $event->id)->where('is_active', true)->get();
        $cart = $request->session()->get($this->cartKey($event), [
            'buyer' => ['name' => '', 'email' => '', 'phone' => ''],
            'items' => [],
            'seats' => [],
        ]);

        [$mySeats, $seatNamesById, $seatLocks] = $this->getMyLockedSeats($event, $request);

        // Derive items from locked seats
        [$derivedItems, $total] = $this->deriveItemsFromSeats($event, $ticketTypes, $mySeats);

        // Promo preview: bila ada promo disimpan di session, validasi dan hitung untuk preview
        $promoCode = (string)$request->session()->get('checkout_promo_code', '');
        $discount = 0; $finalTotal = $total; $promo = null;
        if ($promoCode !== '') {
            [$promo, $discount, $finalTotal, $error] = $this->validatePromoAndCompute($promoCode, $request, $total);
            if ($error) {
                // bersihkan kode tidak valid agar tidak menyesatkan tampilan
                $request->session()->forget('checkout_promo_code');
                $promoCode = '';
                $discount = 0;
                $finalTotal = $total;
            }
        }

        return view('user.checkout', [
            'event' => $event,
            'ticketTypes' => $ticketTypes,
            'cart' => $cart,
            'total' => $total,
            'seatNamesById' => $seatNamesById,
            'seatLocks' => $seatLocks,
            'realTimeSeats' => $mySeats,
            'derivedItems' => $derivedItems,
            'discount' => $discount,
            'finalTotal' => $finalTotal,
            'promo' => $promo,
        ]);
    }

    public function confirm(Event $event, Request $request)
    {
        // Baca buyer info dari form checkout
        $validated = $request->validate([
            'buyer.name' => 'required|string|max:120',
            'buyer.email' => 'required|email',
            'buyer.phone' => 'nullable|string|max:50',
        ]);

        $ticketTypes = TicketType::where('event_id', $event->id)->where('is_active', true)->get();
        [$mySeats, $seatNamesById, $seatLocks] = $this->getMyLockedSeats($event, $request);

        // Semua seat harus masih terkunci oleh sesi ini
        foreach ($mySeats as $sid) {
            if (!isset($seatLocks[$sid]) || $seatLocks[$sid]['by_me'] !== true) {
                return redirect()->route('user.checkout.show', ['event' => $event->id])
                    ->withErrors(['seats' => "Seat $sid tidak lagi tersedia/terkunci oleh Anda."]);
            }
        }

        // Derive items dari seat lalu validasi quota
        [$derivedItems, $total] = $this->deriveItemsFromSeats($event, $ticketTypes, $mySeats);
        foreach ($derivedItems as $item) {
            if ($item['quota'] !== null && $item['qty'] > (int)$item['quota']) {
                return back()->withErrors(['items' => "Stok untuk {$item['name']} tidak mencukupi."]);
            }
        }

        // Validasi promo
        $promoCode = strtoupper(trim((string)$request->input('promo_code', '')));
        $discount = 0; $finalTotal = $total; $promo = null;
        if ($promoCode !== '') {
            [$promo, $discount, $finalTotal, $error] = $this->validatePromoAndCompute($promoCode, $request, $total);
            if ($error) {
                return back()->withErrors(['promo_code' => $error])->withInput();
            }
        }

        // Simpan buyer + ringkasan ke session (opsional)
        $request->session()->put($this->cartKey($event), [
            'buyer' => $validated['buyer'],
            'items' => collect($derivedItems)->mapWithKeys(fn($i)=>[$i['id']=>$i['qty']])->all(),
            'seats' => $mySeats,
        ]);
        // simpan kode promo ke session untuk preview di checkout berikutnya
        if ($promoCode !== '') {
            $request->session()->put('checkout_promo_code', $promoCode);
        }

        // Catat penggunaan promo (agar limit benar-benar bekerja)
        if ($promo) {
            $usage = [
                'promo_id' => $promo->id,
                'user_id' => optional($request->user())->id,
                'session_id' => $request->session()->getId(),
                'used_at' => now(),
            ];
            PromoUsage::create($usage);
            $promo->increment('used_count');
        }

        // Buat Order
        $order = \App\Models\Order::create([
            'event_id' => $event->id,
            'user_id' => optional($request->user())->id,
            'buyer_name' => $validated['buyer']['name'],
            'buyer_email' => $validated['buyer']['email'],
            'buyer_phone' => $validated['buyer']['phone'] ?? null,
            'subtotal' => (int)$total,
            'discount' => (int)$discount,
            'total' => (int)$finalTotal,
            'status' => 'pending',
            'promo_code' => $promoCode ?: null,
            'seats' => $mySeats,
            'items' => $derivedItems,
        ]);

        // Bangun payload Midtrans
        $orderId = 'ORDER-' . $order->id . '-' . now()->format('YmdHis');
        $itemDetails = [];
        foreach ($derivedItems as $it) {
            $itemDetails[] = [
                'id' => (string)$it['id'],
                'price' => (int)$it['price'],
                'quantity' => (int)$it['qty'],
                'name' => (string)$it['name'],
            ];
        }
        if ($discount > 0) {
            $itemDetails[] = [
                'id' => 'PROMO',
                'price' => -(int)$discount,
                'quantity' => 1,
                'name' => 'Diskon Promo',
            ];
        }

        // Hitung total dari item_details
        $grossFromItems = 0;
        foreach ($itemDetails as $it) {
            $grossFromItems += ((int)$it['price']) * ((int)$it['quantity']);
        }

        // Jika ada selisih dengan finalTotal, tambahkan Adjustment agar total match
        $diff = (int)$finalTotal - (int)$grossFromItems;
        if ($diff !== 0) {
            $itemDetails[] = [
                'id' => 'ADJUSTMENT',
                'price' => (int)$diff,
                'quantity' => 1,
                'name' => 'Penyesuaian Total',
            ];
            $grossFromItems += $diff;
        }
        $finalTotal = (int)$grossFromItems;

        $customer = [
            'first_name' => $order->buyer_name,
            'email' => $order->buyer_email,
            'phone' => $order->buyer_phone,
        ];

        // Bangun payload sesuai total final
        $payload = \App\Services\Payments\MidtransGateway::buildPayload(
            $orderId,
            (int)$finalTotal,
            $customer,
            $itemDetails
        );

        // Set finish redirect ke halaman status order
        $payload['callbacks'] = [
            'finish' => route('user.payment.status', ['order' => $order->id]),
        ];

        // Pilihan channel pembayaran (VA multi-bank + e-wallet)
        $requestedChannel = strtolower((string)$request->input('payment_channel', ''));
        $channelMap = [
            'permata' => 'permata_va',
            'bri' => 'bri_va',
            'bca' => 'bca_va',
            'bni' => 'bni_va',
            'gopay' => 'gopay',
            'qris' => 'qris',
            'shopeepay' => 'shopeepay',
        ];

        // Daftar channel default dari config/env (CSV)
        $enabledChannels = (array)config('services.midtrans.enabled_channels', []);
        if (empty($enabledChannels)) {
            $enabledChannels = array_values(array_filter(array_map('trim', explode(',', (string)env(
                'MIDTRANS_ENABLED_CHANNELS',
                'permata_va,gopay,qris,shopeepay,bri_va,bni_va,bca_va'
            )))));
        }

        // Jika user memilih channel spesifik, pakai itu; kalau tidak, tampilkan semua default
        if ($requestedChannel !== '' && isset($channelMap[$requestedChannel])) {
            $payload['enabled_payments'] = [$channelMap[$requestedChannel]];
        } else {
            $payload['enabled_payments'] = $enabledChannels;
        }

        // (Opsional) set masa berlaku VA
        $payload['expiry'] = [
            'start_time' => now()->format('Y-m-d H:i:s O'),
            'unit' => 'days',
            'duration' => 1,
        ];

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $isProd = (bool)config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $gateway = new \App\Services\Payments\MidtransGateway();
        try {
            $snap = $gateway->createSnapTransaction($payload, (string)$serverKey, $isProd);
        } catch (\Throwable $e) {
            return back()->withErrors(['payment' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage()]);
        }

        // Simpan external_ref untuk verifikasi webhook
        $order->update(['external_ref' => $orderId]);

        // Buat Payment
        \App\Models\Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_transaction_id' => $snap['token'] ?? null,
            'status' => 'pending',
            'amount' => (int)$finalTotal,
            'redirect_url' => $snap['redirect_url'] ?? null,
            'raw_payload' => $snap['raw'] ?? [],
        ]);

        // Redirect ke halaman pembayaran Midtrans
        return redirect()->away((string)($snap['redirect_url'] ?? '/'))->with('status', 'Dialihkan ke halaman pembayaran Midtrans.');
    }

    // Endpoint AJAX: terapkan promo real-time di checkout
    public function applyPromo(Event $event, Request $request)
    {
        try {
            $payload = $request->json()->all();
        } catch (\Throwable $e) {
            $payload = $request->all();
        }
        $code = strtoupper(trim((string)($payload['promo_code'] ?? $request->input('promo_code', ''))));

        // Ambil kursi yang terkunci oleh sesi ini sebagai basis subtotal
        [$mySeats] = $this->getMyLockedSeats($event, $request);
        if (empty($mySeats)) {
            return response()->json(['ok' => false, 'error' => 'Tidak ada kursi yang terkunci oleh Anda.'], 400);
        }

        $ticketTypes = \App\Models\TicketType::where('event_id', $event->id)->where('is_active', true)->get();
        [$derivedItems, $total] = $this->deriveItemsFromSeats($event, $ticketTypes, $mySeats);
        if ($total <= 0) {
            return response()->json(['ok' => false, 'error' => 'Subtotal kosong.'], 400);
        }

        // Validasi promo dan hitung diskon
        [$promo, $discount, $finalTotal, $error] = $this->validatePromoAndCompute($code, $request, $total);
        if ($error) {
            return response()->json(['ok' => false, 'error' => $error], 422);
        }

        // Simpan kode untuk preview di sesi
        if ($code !== '') {
            $request->session()->put('checkout_promo_code', $code);
        }

        return response()->json([
            'ok' => true,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'message' => "Promo {$code} diterapkan."
        ]);
    }

    private function deriveItemsFromSeats(Event $event, $ticketTypes, array $seatIds): array
    {
        $map = SeatMap::where('event_id', $event->id)->first();
        $ttBySeat = [];
    
        // Tentukan default ticket type: aktif dengan harga termurah
        $defaultTtId = null;
        $minPrice = PHP_INT_MAX;
        foreach ($ticketTypes as $tt) {
            $price = (int) $tt->price;
            if ($price < $minPrice) {
                $minPrice = $price;
                $defaultTtId = $tt->id;
            }
        }
    
        if ($map && is_array($map->layout)) {
            foreach ($map->layout as $n) {
                $type = $n['type'] ?? null;
                $id = $n['id'] ?? null;
                $ttId = $n['ticket_type_id'] ?? null;
                if (!$id) continue;
    
                if ($type === 'chair') {
                    $ttBySeat[$id] = $ttId ?? null;
                } elseif (is_string($type) && str_starts_with($type, 'table')) {
                    $seatCount = (int)($n['seats'] ?? ($type === 'table6' ? 6 : ($type === 'table4' ? 4 : 0)));
                    for ($idx = 1; $idx <= $seatCount; $idx++) {
                        $seatId = "{$id}-s{$idx}";
                        $ttBySeat[$seatId] = $ttId ?? null; // boleh null, nanti fallback
                    }
                }
            }
        }
    
        $byId = [];
        foreach ($ticketTypes as $tt) {
            $byId[$tt->id] = [
                'id' => $tt->id,
                'name' => $tt->name,
                'price' => (int) $tt->price,
                'quota' => $tt->quota,
            ];
        }
    
        $qtyByType = [];
        foreach ($seatIds as $sid) {
            $ttId = $ttBySeat[$sid] ?? null;
            if (!$ttId && $defaultTtId) {
                // Fallback ke tipe tiket termurah jika seat belum di-assign
                $ttId = $defaultTtId;
            }
            if ($ttId) {
                $qtyByType[$ttId] = ($qtyByType[$ttId] ?? 0) + 1;
            }
        }
    
        $items = [];
        $total = 0;
        foreach ($qtyByType as $ttId => $qty) {
            $meta = $byId[$ttId] ?? null;
            if (!$meta) continue;
            $items[] = [
                'id' => $ttId,
                'name' => $meta['name'],
                'qty' => $qty,
                'price' => $meta['price'],
                'quota' => $meta['quota'],
            ];
            $total += $meta['price'] * $qty;
        }
    
        return [$items, $total];
    }

    private function getMyLockedSeats(Event $event, Request $request): array
    {
        $sessionId = $request->session()->getId();
        $seatMap = SeatMap::where('event_id', $event->id)->first();
        $mySeats = [];
        $seatNamesById = [];
        $seatLocks = [];
    
        if ($seatMap && is_array($seatMap->layout)) {
            foreach ($seatMap->layout as $n) {
                $type = $n['type'] ?? null;
                $id = $n['id'] ?? null;
                if (!$id) continue;
    
                if ($type === 'chair') {
                    $key = "seat_lock:{$event->id}:{$id}";
                    try { $val = Redis::get($key); } catch (\Throwable $e) { $val = null; }
                    if ($val !== null) {
                        $seatLocks[$id] = ['by_me' => ($val === $sessionId), 'by' => $val];
                        if ($val === $sessionId) { $mySeats[] = $id; }
                    }
                    $seatNamesById[$id] = ($n['display_name'] ?? ($n['label'] ?? 'Kursi'));
                } elseif (is_string($type) && str_starts_with($type, 'table')) {
                    $seatCount = (int)($n['seats'] ?? ($type === 'table6' ? 6 : ($type === 'table4' ? 4 : 0)));
                    $baseName = ($n['display_name'] ?? ($n['label'] ?? 'Meja')) ?: 'Meja';
                    for ($idx = 1; $idx <= $seatCount; $idx++) {
                        $seatId = "{$id}-s{$idx}";
                        $key = "seat_lock:{$event->id}:{$seatId}";
                        try { $val = Redis::get($key); } catch (\Throwable $e) { $val = null; }
                        if ($val !== null) {
                            $seatLocks[$seatId] = ['by_me' => ($val === $sessionId), 'by' => $val];
                            if ($val === $sessionId) { $mySeats[] = $seatId; }
                        }
                        $seatNamesById[$seatId] = "{$baseName} - {$idx}";
                    }
                }
            }
        }
    
        // Fallback: tambahkan lock dari session jika Redis tidak aktif
        $sessionSeats = (array)$request->session()->get('fallback_locked_seats:' . $event->id, []);
        foreach ($sessionSeats as $sid) {
            if (!isset($seatLocks[$sid])) {
                $seatLocks[$sid] = ['by_me' => true, 'by' => $sessionId];
            }
            if (!in_array($sid, $mySeats, true)) { $mySeats[] = $sid; }
        }
    
        return [$mySeats, $seatNamesById, $seatLocks];
    }

    private function validatePromoAndCompute(string $promoCode, Request $request, int $total): array
    {
        $code = strtoupper(trim($promoCode));
        if ($code === '') { return [null, 0, $total, null]; }

        // Pencarian kode ternormalisasi (hindari spasi tersembunyi/case mismatch)
        $promo = Promo::query()->byNormalizedCode($code)->first();
        if (!$promo || !$promo->is_active) {
            return [null, 0, $total, 'Kode promo tidak valid atau tidak aktif.'];
        }

        $now = now();
        if ($promo->starts_at && $now->lt($promo->starts_at)) {
            return [$promo, 0, $total, 'Promo belum aktif.'];
        }
        if ($promo->expires_at && $now->gt($promo->expires_at)) {
            return [$promo, 0, $total, 'Promo sudah kedaluwarsa.'];
        }

        // Limit total
        if ($promo->usage_limit_total !== null && $promo->used_count >= $promo->usage_limit_total) {
            return [$promo, 0, $total, 'Promo sudah mencapai batas penggunaan total.'];
        }

        // Limit per user/sesi
        $userId = optional($request->user())->id;
        $sessionId = $request->session()->getId();
        $userUsageCount = \App\Models\PromoUsage::query()
            ->where('promo_id', $promo->id)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('session_id', $sessionId))
            ->count();

        if ($promo->usage_limit_per_user !== null && $userUsageCount >= $promo->usage_limit_per_user) {
            return [$promo, 0, $total, 'Anda sudah mencapai batas penggunaan promo ini.'];
        }

        // Hitung diskon
        $discount = 0;
        if ($promo->type === 'percent') {
            $rate = max(0, min(100, (int)$promo->value));
            $discount = (int) floor($total * $rate / 100);
        } else { // nominal
            $discount = max(0, (int)$promo->value);
        }
        $discount = min($discount, $total);
        $finalTotal = $total - $discount;

        return [$promo, $discount, $finalTotal, null];
    }

    // Halaman status pembayaran
    public function paymentStatus(\App\Models\Order $order)
    {
        $payment = \App\Models\Payment::where('order_id', $order->id)->latest()->first();
        return view('user.payment_status', [
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    // Tombol "Check Status": tarik status terbaru dari Midtrans API dan sinkronkan
    public function checkPaymentStatus(\App\Models\Order $order, Request $request)
    {
        $externalRef = $order->external_ref;
        if (!$externalRef) {
            return back()->withErrors(['status' => 'External ref tidak tersedia untuk order ini.']);
        }

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        $isProd = (bool)config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $base = $isProd ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
        try {
            $res = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->get($base . "/v2/{$externalRef}/status");

            if (!$res->successful()) {
                return back()->withErrors(['status' => 'Gagal mengambil status: ' . $res->body()]);
            }

            $p = $res->json();
            // Map ke status internal (selaras dengan webhook)
            $trxStatus = $p['transaction_status'] ?? 'pending';
            $fraudStatus = $p['fraud_status'] ?? null;

            $targetPaymentStatus = 'pending';
            $targetOrderStatus = 'pending';

            if (in_array($trxStatus, ['capture','settlement'], true) && ($fraudStatus === null || $fraudStatus === 'accept')) {
                $targetPaymentStatus = 'paid';
                $targetOrderStatus = 'paid';
            } elseif (in_array($trxStatus, ['cancel','deny'], true)) {
                $targetPaymentStatus = 'failed';
                $targetOrderStatus = 'failed';
            } elseif ($trxStatus === 'expire') {
                $targetPaymentStatus = 'expired';
                $targetOrderStatus = 'expired';
            }

            $payment = \App\Models\Payment::where('order_id', $order->id)->latest()->first();
            if ($payment) {
                $payment->update([
                    'provider_transaction_id' => $p['transaction_id'] ?? $payment->provider_transaction_id,
                    'status' => $targetPaymentStatus,
                    'raw_payload' => $p,
                ]);
            }
            $order->update(['status' => $targetOrderStatus]);

            return back()->with('status', 'Status diperbarui: ' . $targetOrderStatus);
        } catch (\Throwable $e) {
            return back()->withErrors(['status' => 'Error: ' . $e->getMessage()]);
        }
    }
}