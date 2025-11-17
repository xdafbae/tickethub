<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;

class LandingController extends Controller
{
    public function index()
    {
        $upcomingEvents = Event::query()
            ->whereNotNull('date')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        return view('user.landing', compact('upcomingEvents'));
    }
}