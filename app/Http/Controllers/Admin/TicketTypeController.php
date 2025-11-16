<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketTypeController extends Controller
{
    public function index()
    {
        $types = TicketType::with('event')->orderBy('event_id')->orderBy('name')->paginate(10);
        return view('admin.ticket_types.index', compact('types'));
    }

    public function create()
    {
        $events = Event::orderByDesc('date')->get();
        return view('admin.ticket_types.create', compact('events'));
    }

    public function store(Request $request)
    {
        $rawInput = $request->filled('price') ? $request->input('price') : $request->input('price_view');
        $raw = is_string($rawInput) ? preg_replace('/\D+/', '', $rawInput) : null;
        if ($raw !== null && $raw !== '') { $request->merge(['price' => $raw]); }
        $activeInput = $request->input('is_active');
        if (is_array($activeInput)) { $activeInput = end($activeInput); }
        if ($activeInput !== null) {
            $request->merge(['is_active' => in_array($activeInput, ['1', 1, true, 'true'], true) ? 1 : 0]);
        }
        $data = $request->validate([
            'event_id' => ['required','exists:events,id'],
            'name' => ['required','in:VIP,Reguler,Early-bird'],
            'price' => ['required','integer','min:0'],
            'quota' => ['required','integer','min:0'],
            'description' => ['nullable','string'],
            'is_active' => ['nullable','in:0,1'],
            'available_from' => ['nullable','date'],
            'available_to' => [
                'nullable','date',
                Rule::when($request->filled('available_from'), 'after_or_equal:available_from')
            ],
        ]);
        $data['is_active'] = (int)($request->input('is_active', 1));
        TicketType::create($data);
        return redirect()->route('admin.ticket_types.index')->with('status','Tipe tiket berhasil dibuat');
    }

    public function show(TicketType $ticket_type)
    {
        $ticket_type->load('event');
        return view('admin.ticket_types.show', ['type' => $ticket_type]);
    }

    public function edit(TicketType $ticket_type)
    {
        $events = Event::orderByDesc('date')->get();
        return view('admin.ticket_types.edit', ['type' => $ticket_type, 'events' => $events]);
    }

    public function update(Request $request, TicketType $ticket_type)
    {
        $rawInput = $request->filled('price') ? $request->input('price') : $request->input('price_view');
        $raw = is_string($rawInput) ? preg_replace('/\D+/', '', $rawInput) : null;
        if ($raw !== null && $raw !== '') { $request->merge(['price' => $raw]); }
        $activeInput = $request->input('is_active');
        if (is_array($activeInput)) { $activeInput = end($activeInput); }
        if ($activeInput !== null) {
            $request->merge(['is_active' => in_array($activeInput, ['1', 1, true, 'true'], true) ? 1 : 0]);
        }
        $data = $request->validate([
            'event_id' => ['required','exists:events,id'],
            'name' => ['required','in:VIP,Reguler,Early-bird'],
            'price' => ['required','integer','min:0'],
            'quota' => ['required','integer','min:0'],
            'description' => ['nullable','string'],
            'is_active' => ['nullable','in:0,1'],
            'available_from' => ['nullable','date'],
            'available_to' => [
                'nullable','date',
                Rule::when($request->filled('available_from'), 'after_or_equal:available_from')
            ],
        ]);
        $data['is_active'] = (int)($request->input('is_active', $ticket_type->is_active));
        $ticket_type->update($data);
        return redirect()->route('admin.ticket_types.index')->with('status','Tipe tiket berhasil diperbarui');
    }

    public function destroy(TicketType $ticket_type)
    {
        $ticket_type->delete();
        return redirect()->route('admin.ticket_types.index')->with('status','Tipe tiket berhasil dihapus');
    }
}