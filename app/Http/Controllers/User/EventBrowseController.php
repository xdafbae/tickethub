<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SeatMap;
use Illuminate\Http\Request;

class EventBrowseController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim((string)$request->query('q', ''));
        $category = (string)$request->query('category', '');
        $location = trim((string)$request->query('location', ''));
        $start    = $request->query('start');
        $end      = $request->query('end');

        $events = Event::query()
            ->when($q !== '', function ($qry) use ($q) {
                $qry->where(function($w) use ($q){
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%");
                });
            })
            ->when($category !== '', fn($qry) => $qry->where('category', $category))
            ->when($location !== '', fn($qry) => $qry->where('location', 'like', "%{$location}%"))
            ->when($start, fn($qry) => $qry->whereDate('date', '>=', $start))
            ->when($end, fn($qry) => $qry->whereDate('date', '<=', $end))
            ->orderBy('date')
            ->paginate(9)
            ->withQueryString();

        // daftar kategori statis (bisa diambil distinct dari DB jika perlu)
        $categories = ['Music','Sports','Theater','Technology'];

        return view('user.events.index', compact('events', 'categories', 'q', 'category', 'location', 'start', 'end'));
    }
    public function show(Event $event)
    {
        $event->load('ticketTypes');

        $seatMap = SeatMap::where('event_id', $event->id)->first();

        return view('user.events.show', compact('event', 'seatMap'));
    }
}