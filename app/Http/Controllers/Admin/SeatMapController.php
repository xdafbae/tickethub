<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SeatMap;
use Illuminate\Http\Request;

class SeatMapController extends Controller
{
    public function builder(Event $event)
    {
        $seatMap = SeatMap::firstOrCreate(['event_id' => $event->id]);
        $event->load('ticketTypes');
        $types = $event->ticketTypes;
        return view('admin.seat_map.builder', compact('event','seatMap','types'));
    }

    public function save(Event $event, Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'layout' => ['required'],
        ]);
        $layout = $data['layout'];
        if (is_string($layout)) {
            $decoded = json_decode($layout, true);
            $layout = is_array($decoded) ? $decoded : [];
        }
        $seatMap = SeatMap::firstOrCreate(['event_id' => $event->id]);
        $seatMap->name = $data['name'];
        $seatMap->layout = $layout;
        $seatMap->save();
        return redirect()->route('admin.seat_map.builder', $event)->with('status','Seat map berhasil disimpan');
    }
}