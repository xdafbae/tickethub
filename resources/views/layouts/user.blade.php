<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TicketHub') - TicketHub</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}?v={{ filemtime(public_path('css/admin-styles.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ filemtime(public_path('css/styles.css')) }}">

    @yield('additional-css')
</head>
<body>
    <header style="position:sticky; top:0; z-index:50; background:#0b0b0b; border-bottom:1px solid var(--admin-border);">
        <div class="container" style="max-width:1100px; margin:0 auto; padding:10px 16px; display:flex; align-items:center; justify-content:space-between;">
            <a href="{{ url('/') }}" style="font-weight:800; font-size:18px; text-decoration:none; color:#fff;">
                TicketHub
            </a>
            <nav style="display:flex; gap:16px; align-items:center;">
                <a href="{{ route('user.events.index') }}" class="btn btn-sm btn-outline">Event</a>
                @auth
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-secondary"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-secondary">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @hasSection('page-title')
            <div style="border-bottom:1px solid var(--admin-border); background: rgba(255,255,255,0.02);">
                <div class="container" style="max-width:1100px; margin:0 auto; padding:16px;">
                    <h2 style="margin:0;">@yield('page-title')</h2>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="border-top:1px solid var(--admin-border); margin-top:24px;">
        <div class="container" style="max-width:1100px; margin:0 auto; padding:16px; color:var(--admin-muted); display:flex; justify-content:space-between;">
            <span>&copy; {{ date('Y') }} TicketHub</span>
            <div style="display:flex; gap:12px;">
                <a href="#" style="color:var(--admin-muted);">Ketentuan</a>
                <a href="#" style="color:var(--admin-muted);">Privasi</a>
                <a href="#" style="color:var(--admin-muted);">Kontak</a>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/admin-script.js') }}?v={{ filemtime(public_path('js/admin-script.js')) }}"></script>
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
    @yield('additional-js')
</body>
</html>