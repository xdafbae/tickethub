<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function dashboard(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->subDays(30)->format('Y-m-d')))->startOfDay();
        $to   = Carbon::parse($request->input('to', now()->format('Y-m-d')))->endOfDay();
        $eventId = $request->input('event_id');

        $ordersPaidQuery = Order::whereBetween('created_at', [$from, $to])
            ->where('status', 'paid')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId));

        $ordersPaid = $ordersPaidQuery
            ->with('event')
            ->get();

        $totalRevenuePaid = (int) $ordersPaid->sum('total');

        $refundAmount = (int) Payment::where('status', 'refunded')
            ->whereBetween('created_at', [$from, $to])
            ->when($eventId, function ($q) use ($eventId) {
                $q->whereHas('order', fn($oq) => $oq->where('event_id', $eventId));
            })
            ->sum('amount');

        $netRevenue = $totalRevenuePaid - $refundAmount;
        $ordersCountPaid = (int) $ordersPaid->count();
        $ticketsSold = $this->ticketsCountForOrders($ordersPaid);

        // Per-event aggregation (paid only)
        $perEvent = $ordersPaid->groupBy('event_id')->map(function (Collection $group) {
            return [
                'event'   => optional($group->first()->event)->title ?? 'Unknown',
                'orders'  => $group->count(),
                'revenue' => (int) $group->sum('total'),
                'tickets' => $this->ticketsCountForOrders($group),
            ];
        })->values()->sortByDesc('revenue')->all();

        // Daily net revenue (paid - refunds)
        $paidByDay = Order::selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'paid')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $refundByDay = Payment::selectRaw('DATE(created_at) as day, SUM(amount) as refund')
            ->where('status', 'refunded')
            ->whereBetween('created_at', [$from, $to])
            ->when($eventId, function ($q) use ($eventId) {
                $q->whereHas('order', fn($oq) => $oq->where('event_id', $eventId));
            })
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $period = CarbonPeriod::create($from, $to);
        $dailyLabels = [];
        $dailyNetRevenue = [];
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $paid = (int) ($paidByDay[$day]->revenue ?? 0);
            $ref  = (int) ($refundByDay[$day]->refund ?? 0);
            $dailyLabels[] = $date->format('d/m');
            $dailyNetRevenue[] = $paid - $ref;
        }

        $events = Event::orderBy('date', 'desc')->get();

        return view('admin.reports.dashboard', [
            'from' => $from,
            'to' => $to,
            'eventId' => $eventId,
            'events' => $events,
            'netRevenue' => $netRevenue,
            'ordersCountPaid' => $ordersCountPaid,
            'ticketsSold' => $ticketsSold,
            'perEvent' => $perEvent,
            'dailyLabels' => $dailyLabels,
            'dailyNetRevenue' => $dailyNetRevenue,
        ]);
    }

    public function export(Request $request, string $format)
    {
        $format = strtolower($format);
        if (!in_array($format, ['csv', 'xls'])) {
            abort(400, 'Format tidak didukung.');
        }

        $from = Carbon::parse($request->input('from', now()->subDays(30)->format('Y-m-d')))->startOfDay();
        $to   = Carbon::parse($request->input('to', now()->format('Y-m-d')))->endOfDay();
        $eventId = $request->input('event_id');

        $orders = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['paid', 'refunded', 'canceled', 'failed', 'expired'])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->with('event')
            ->orderByDesc('id')
            ->get();

        $rows = [];
        $rows[] = ['OrderID', 'Tanggal', 'Event', 'Pembeli', 'Email', 'Total', 'Status', 'Tiket'];
        foreach ($orders as $o) {
            $rows[] = [
                $o->id,
                optional($o->created_at)->format('Y-m-d H:i'),
                optional($o->event)->title ?? 'Unknown',
                $o->buyer_name,
                $o->buyer_email,
                $o->total,
                $o->status,
                $this->ticketsCountForOrders(collect([$o])),
            ];
        }

        $filename = 'orders_report_' . $from->format('Ymd') . '_' . $to->format('Ymd') . ($eventId ? "_event{$eventId}" : '') . '.' . $format;

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            if ($format === 'csv') {
                fputcsv($handle, $row);
            } else {
                // Untuk xls sederhana: gunakan tab-separated agar mudah dibuka di Excel
                fwrite($handle, implode("\t", $row) . "\n");
            }
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $mime = $format === 'csv' ? 'text/csv' : 'application/vnd.ms-excel';

        return Response::make($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function ticketsCountForOrders(Collection $orders): int
    {
        $total = 0;
        foreach ($orders as $o) {
            $items = is_array($o->items) ? $o->items : [];
            if (!empty($items)) {
                foreach ($items as $it) {
                    $qty = is_array($it) ? ($it['qty'] ?? 0) : (is_object($it) ? ($it->qty ?? 0) : 0);
                    $total += (int) $qty;
                }
            } elseif (is_array($o->seats)) {
                $total += count($o->seats);
            }
        }
        return $total;
    }
}