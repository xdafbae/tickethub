<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderByDesc('date')->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'category' => ['required','string','max:100'],
            'date' => ['required','date'],
            'location' => ['required','string','max:255'],
            'quota' => ['required','integer','min:1'],
            'description' => ['nullable','string'],
            'poster' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('events/posters', 'public');
        }

        $event = Event::create($data);

        return redirect()->route('admin.events.index')->with('status', 'Event berhasil dibuat');
    }

    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'category' => ['required','string','max:100'],
            'date' => ['required','date'],
            'location' => ['required','string','max:255'],
            'quota' => ['required','integer','min:1'],
            'description' => ['nullable','string'],
            'poster' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $data['poster'] = $request->file('poster')->store('events/posters', 'public');
        }

        $event->update($data);

        return redirect()->route('admin.events.index')->with('status', 'Event berhasil diperbarui');
    }

    public function destroy(Event $event)
    {
        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }
        $event->delete();
        return redirect()->route('admin.events.index')->with('status', 'Event berhasil dihapus');
    }
}