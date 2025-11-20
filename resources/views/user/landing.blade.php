@extends('layouts.user')

@section('title', 'Discover Amazing Events')
@section('page-title', '')

@section('content')
<section class="pt-12 pb-24 overflow-x-hidden">
    <div class="container mx-auto text-center space-y-4">
        <h1 class="text-5xl font-extrabold tracking-tight text-white">
            Discover Amazing <span class="gradient-text">Events Near You</span>
        </h1>
        <p class="text-white/70 text-base">
            Find and book tickets to concerts, conferences, festivals, sports events, and more.<br>
            Don't miss out on unforgettable experiences.
        </p>
        <div class="flex justify-center mt-6">
            <form method="GET" action="{{ route('user.events.index') }}" class="landing-search-form">
                <input type="text" name="q" placeholder="Search events, location..." class="landing-search-input" />
                <button type="submit" class="u-btn-primary text-sm">Search</button>
            </form>
        </div>

        <div class="grid grid-cols-3 gap-6 mt-8 max-w-2xl mx-auto">
            <div>
                <div class="text-orange-400 text-3xl font-extrabold">1000+</div>
                <div class="text-white/70 text-sm">Events</div>
            </div>
            <div>
                <div class="text-pink-400 text-3xl font-extrabold">250K+</div>
                <div class="text-white/70 text-sm">Attendees</div>
            </div>
            <div>
                <div class="text-red-400 text-3xl font-extrabold">50+</div>
                <div class="text-white/70 text-sm">Cities</div>
            </div>
        </div>
    </div>
</section>

<section class="py-16" id="upcoming">
    <div class="container mx-auto">
        <h2 class="text-center text-3xl font-extrabold mb-8">Upcoming Events</h2>

        @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
            <div class="grid md:grid-cols-3 grid-cols-1 gap-6" id="eventsGrid">
                @foreach($upcomingEvents as $ev)
                    <article class="event-card">
                        <img src="{{ $ev->poster ? asset('storage/'.$ev->poster) : 'https://picsum.photos/seed/'.$ev->id.'/800/480' }}" alt="{{ $ev->title }}">
                        <div class="event-card-body">
                            <h3 class="event-card-title">{{ $ev->title }}</h3>
                            <div class="event-card-meta">
                                <span>📅 {{ $ev->date?->format('d M Y') }}</span>
                                <span>📍 {{ $ev->location }}</span>
                            </div>
                            <p class="event-card-desc">
                                {{ $ev->category }} • Jangan lewatkan pengalaman seru ini.
                            </p>
                            <div class="event-card-footer" style="justify-content:flex-end;">
                                <a href="{{ route('user.events.show', $ev) }}" class="event-btn">Lihat Detail</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="flex justify-center mt-8">
                <a href="{{ route('user.events.index') }}" class="u-btn-secondary">Lihat Semua Event</a>
            </div>
        @else
            <div class="text-center text-white/70">Belum ada event yang akan datang.</div>
        @endif
    </div>
</section>

<section class="py-16">
    <div class="container mx-auto">
        <h2 class="text-center text-3xl font-extrabold mb-8">Browse by Category</h2>
        <div class="grid md:grid-cols-4 grid-cols-2 gap-6">
            <a href="{{ route('user.events.index', ['category' => 'Music']) }}" class="category-btn">Music</a>
            <a href="{{ route('user.events.index', ['category' => 'Sports']) }}" class="category-btn">Sports</a>
            <a href="{{ route('user.events.index', ['category' => 'Theater']) }}" class="category-btn">Theater</a>
            <a href="{{ route('user.events.index', ['category' => 'Technology']) }}" class="category-btn">Technology</a>
        </div>

        <div class="flex justify-center mt-8">
            <a href="{{ route('user.events.index') }}" class="u-btn-secondary">Explore All Events</a>
        </div>
    </div>
</section>
@endsection