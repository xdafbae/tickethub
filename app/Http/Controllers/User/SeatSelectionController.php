<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SeatMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SeatSelectionController extends Controller
{
    public function map(Event $event, Request $request)
    {
        $seatMap = SeatMap::where('event_id', $event->id)->first();
        if (! $seatMap || !is_array($seatMap->layout) || !count($seatMap->layout)) {
            return response()->json(['layout' => [], 'locks' => []]);
        }

        $layout = $seatMap->layout;
        $minX = max(0, min(array_map(fn($n)=> (int)($n['x'] ?? 0), $layout)));
        $minY = max(0, min(array_map(fn($n)=> (int)($n['y'] ?? 0), $layout)));

        $typeNames = $event->ticketTypes()->pluck('name', 'id')->all();

        $sessionId = $request->session()->getId();
        $nodes = [];
        $locks = [];

        foreach ($layout as $n) {
            $id = $n['id'] ?? null;
            $type = $n['type'] ?? 'node';
            $ticketTypeId = $n['ticket_type_id'] ?? null;

            $x = max(0, (int)($n['x'] ?? 0) - $minX);
            $y = max(0, (int)($n['y'] ?? 0) - $minY);
            $w = (int)($n['w'] ?? 110);
            $h = (int)($n['h'] ?? 80);

            $nodes[] = [
                'id' => $id,
                'type' => $type,
                'label' => $n['label'] ?? '',
                'x' => max(0, (int)($n['x'] ?? 0) - $minX),
                'y' => max(0, (int)($n['y'] ?? 0) - $minY),
                'w' => (int)($n['w'] ?? 110),
                'h' => (int)($n['h'] ?? 80),
                'ticket_type_id' => $ticketTypeId,
                'ticket_type_name' => $ticketTypeId ? ($typeNames[$ticketTypeId] ?? null) : null,
                'disabled' => (bool)($n['disabled'] ?? false),
            ];

            // Tambah nama tampilan untuk kursi builder
            if ($type === 'chair' && $id) {
                $nodes[count($nodes)-1]['display_name'] = ($n['label'] ?? '') !== '' ? $n['label'] : 'Kursi';
                $key = self::lockKey($event->id, $id);
                try { $val = Redis::get($key); } catch (\Throwable $e) { $val = null; }
                if ($val !== null) {
                    $locks[$id] = ['by_me' => ($val === $sessionId), 'by' => $val];
                }
            }

            // Ekspansi kursi dari meja (jika ada seats)
            if (str_starts_with($type, 'table')) {
                $seatCount = (int)($n['seats'] ?? ( $type === 'table6' ? 6 : ( $type === 'table4' ? 4 : 0) ));
                if ($seatCount > 0 && $id) {
                    $seatSize = 22;
                    $margin   = 6;
                    $cxMid    = $x + $w / 2;
                    $cyMid    = $y + $h / 2;

                    $positions = [];
                    if ($seatCount === 4) {
                        $positions = [
                            ['cx' => $cxMid,           'cy' => $y - $margin - $seatSize/2],
                            ['cx' => $cxMid,           'cy' => $y + $h + $margin + $seatSize/2],
                            ['cx' => $x - $margin - $seatSize/2,      'cy' => $cyMid],
                            ['cx' => $x + $w + $margin + $seatSize/2, 'cy' => $cyMid],
                        ];
                    } else {
                        $positions = [
                            ['cx' => $x + $w*0.25, 'cy' => $y - $margin - $seatSize/2],
                            ['cx' => $x + $w*0.75, 'cy' => $y - $margin - $seatSize/2],
                            ['cx' => $x + $w*0.25, 'cy' => $y + $h + $margin + $seatSize/2],
                            ['cx' => $x + $w*0.75, 'cy' => $y + $h + $margin + $seatSize/2],
                            ['cx' => $x - $margin - $seatSize/2,      'cy' => $cyMid],
                            ['cx' => $x + $w + $margin + $seatSize/2, 'cy' => $cyMid],
                        ];
                    }

                    $idx = 1;
                    foreach ($positions as $p) {
                        $seatId = "{$id}-s{$idx}";
                        $sx = (int)round($p['cx'] - $seatSize/2);
                        $sy = (int)round($p['cy'] - $seatSize/2);

                        $nodes[] = [
                            'id' => $seatId,
                            'type' => 'chair',
                            'label' => '',
                            'display_name' => (($n['label'] ?? 'Meja') ?: 'Meja') . ' - ' . $idx,
                            'x' => max(0, $sx),
                            'y' => max(0, $sy),
                            'w' => $seatSize,
                            'h' => $seatSize,
                            'ticket_type_id' => $ticketTypeId,
                            'ticket_type_name' => $ticketTypeId ? ($typeNames[$ticketTypeId] ?? null) : null,
                            'disabled' => (bool)($n['disabled'] ?? false),
                        ];

                        $key = self::lockKey($event->id, $seatId);
                        try { $val = Redis::get($key); } catch (\Throwable $e) { $val = null; }
                        if ($val !== null) {
                            $locks[$seatId] = ['by_me' => ($val === $sessionId), 'by' => $val];
                        }

                        $idx++;
                    }
                }
            }
        }

        return response()->json([
            'layout' => $nodes,
            'locks' => $locks,
        ]);
    }

    public function lock(Event $event, Request $request)
    {
        $seats = (array)$request->input('seats', []);
        $ttl = (int)$request->input('ttl', 120);
        $sessionId = $request->session()->getId();

        $locked = [];
        $failed = [];

        foreach ($seats as $seatId) {
            $key = self::lockKey($event->id, $seatId);
            try {
                $current = Redis::get($key);
            } catch (\Throwable $e) {
                $failed[] = $seatId;
                continue;
            }

            if ($current === null) {
                try {
                    $ok = Redis::set($key, $sessionId, 'EX', $ttl, 'NX');
                    if ($ok) {
                        $locked[] = $seatId;
                    } else {
                        $failed[] = $seatId;
                    }
                } catch (\Throwable $e) {
                    $failed[] = $seatId;
                }
            } elseif ($current === $sessionId) {
                try {
                    Redis::expire($key, $ttl);
                    $locked[] = $seatId;
                } catch (\Throwable $e) {
                    $failed[] = $seatId;
                }
            } else {
                $failed[] = $seatId;
            }
        }

        return response()->json([
            'locked' => $locked,
            'failed' => $failed,
            'ttl' => $ttl,
        ]);
    }

    public function unlock(Event $event, Request $request)
    {
        $seats = (array)$request->input('seats', []);
        $sessionId = $request->session()->getId();

        $released = [];
        $skipped = [];

        foreach ($seats as $seatId) {
            $key = self::lockKey($event->id, $seatId);
            try {
                $current = Redis::get($key);
            } catch (\Throwable $e) {
                $skipped[] = $seatId;
                continue;
            }

            if ($current === $sessionId) {
                try {
                    Redis::del($key);
                    $released[] = $seatId;
                } catch (\Throwable $e) {
                    $skipped[] = $seatId;
                }
            } else {
                $skipped[] = $seatId;
            }
        }

        return response()->json([
            'released' => $released,
            'skipped' => $skipped,
        ]);
    }

    private static function lockKey(int $eventId, string $seatId): string
    {
        return "seat_lock:{$eventId}:{$seatId}";
    }
}