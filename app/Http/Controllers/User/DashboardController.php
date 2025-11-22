<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::with(['event', 'payments'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString(); // jaga query string (filter/sort) tetap terbawa

        return view('user.dashboard', compact('orders', 'user'));
    }

    public function orders(Request $request)
    {
        // Alias ke index untuk konsistensi route
        return $this->index($request);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id !== $user->id && !$user->hasAnyRole(['admin', 'gate_staff'])) {
            abort(403);
        }

        $order->load(['event', 'payments']);
        return view('user.orders.show', compact('order', 'user'));
    }
}