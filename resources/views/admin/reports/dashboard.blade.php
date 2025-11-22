@extends('layouts.admin')

@section('content')
@role('admin')
<div class="table-card" style="display:flex; flex-direction:column; gap:16px;">
    <div class="table-header" style="justify-content:space-between; align-items:center;">
        <h3>Dashboard Admin & Reporting</h3>
        <div style="display:flex; gap:8px;">
            <a class="btn btn-secondary btn-sm"
               href="{{ route('admin.reports.export', ['format' => 'csv', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'event_id' => $eventId]) }}">Export CSV</a>
            <a class="btn btn-secondary btn-sm"
               href="{{ route('admin.reports.export', ['format' => 'xls', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'event_id' => $eventId]) }}">Export XLS</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.dashboard') }}" style="display:grid; grid-template-columns: repeat(4,1fr); gap:12px;">
        <div>
            <label class="form-label">Dari</label>
            <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Sampai</label>
            <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Event</label>
            <select name="event_id" class="form-input">
                <option value="">Semua Event</option>
                @foreach($events as $e)
                    <option value="{{ $e->id }}" {{ (string)$eventId === (string)$e->id ? 'selected' : '' }}>{{ $e->title }} ({{ optional($e->date)->format('d/m/Y') }})</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; align-items:flex-end; gap:8px;">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.reports.dashboard') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:12px;">
        <div class="table-card" style="padding:16px;">
            <div style="color:var(--admin-muted); font-size:12px;">Net Revenue</div>
            <div style="font-size:22px; font-weight:700;">Rp {{ number_format($netRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="table-card" style="padding:16px;">
            <div style="color:var(--admin-muted); font-size:12px;">Orders Paid</div>
            <div style="font-size:22px; font-weight:700;">{{ number_format($ordersCountPaid) }}</div>
        </div>
        <div class="table-card" style="padding:16px;">
            <div style="color:var(--admin-muted); font-size:12px;">Tickets Sold</div>
            <div style="font-size:22px; font-weight:700;">{{ number_format($ticketsSold) }}</div>
        </div>
    </div>

    <div class="table-card" style="padding:16px;">
        <h4 style="margin-bottom:6px;">Net Revenue per Hari</h4>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    <div class="table-card" style="padding:16px;">
        <h4 style="margin-bottom:6px;">Ticket Sales per Event</h4>
        <canvas id="eventChart" height="80"></canvas>
    </div>

    <div class="table-card">
        <div class="table-header"><h3>Penjualan per Event</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th style="text-align:right;">Tickets</th>
                        <th style="text-align:right;">Orders</th>
                        <th style="text-align:right;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @php($totTickets = 0)
                    @php($totOrders = 0)
                    @php($totRevenue = 0)
                    @foreach($perEvent as $row)
                        @php($totTickets += $row['tickets'])
                        @php($totOrders += $row['orders'])
                        @php($totRevenue += $row['revenue'])
                        <tr>
                            <td>{{ $row['event'] }}</td>
                            <td style="text-align:right;">{{ number_format($row['tickets']) }}</td>
                            <td style="text-align:right;">{{ number_format($row['orders']) }}</td>
                            <td style="text-align:right;">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th style="text-align:right;">{{ number_format($totTickets) }}</th>
                        <th style="text-align:right;">{{ number_format($totOrders) }}</th>
                        <th style="text-align:right;">Rp {{ number_format($totRevenue, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endrole
@endsection

@section('additional-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function(){
        // Line chart: net revenue per day
        var revCtx = document.getElementById('revenueChart').getContext('2d');
        var revChart = new Chart(revCtx, {
            type: 'line',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Net Revenue',
                    data: @json($dailyNetRevenue),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.1)',
                    tension: 0.25,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
                }
            }
        });

        // Bar chart: tickets sold per event
        var events = @json(array_map(fn($r) => $r['event'], $perEvent));
        var tickets = @json(array_map(fn($r) => $r['tickets'], $perEvent));
        var evtCtx = document.getElementById('eventChart').getContext('2d');
        var evtChart = new Chart(evtCtx, {
            type: 'bar',
            data: {
                labels: events,
                datasets: [{
                    label: 'Tickets Sold',
                    data: tickets,
                    backgroundColor: '#10b981'
                }]
            },
            options: {
                indexAxis: 'x',
                responsive: true,
                plugins: { legend: { display: true } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    })();
</script>
@endsection