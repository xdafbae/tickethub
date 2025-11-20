<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - EventHub</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Google Fonts - Poppins for modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}?v={{ filemtime(public_path('css/admin-styles.css')) }}">
    
    @yield('additional-css')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        @include('components.sidebar')

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            @include('components.header')

            <!-- Page Content -->
            <div class="admin-content">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Modals -->
    @yield('modals')

    @if(session('status'))
    <div id="successModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: none; align-items: center; justify-content: center; z-index: 1000;">
        <div style="width: 100%; max-width: 420px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--admin-border); font-weight: 600;">Berhasil</div>
            <div style="padding: 20px; color: #222;">{{ session('status') }}</div>
            <div style="padding: 14px 20px; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--admin-border);">
                <button type="button" id="successClose" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    <div id="confirmModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: none; align-items: center; justify-content: center; z-index: 1000;">
        <div style="width: 100%; max-width: 420px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--admin-border); font-weight: 600;">Konfirmasi</div>
            <div style="padding: 20px; color: #222;">Yakin ingin menghapus data ini?</div>
            <div style="padding: 14px 20px; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--admin-border);">
                <button type="button" id="confirmCancel" class="btn btn-secondary">Batal</button>
                <button type="button" id="confirmOk" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/admin-script.js') }}"></script>
    @if(session('status'))
    <script>
        (function(){
            var m = document.getElementById('successModal');
            if (m) {
                m.style.display = 'flex';
                var c = document.getElementById('successClose');
                if (c) c.addEventListener('click', function(){ m.style.display = 'none'; });
                m.addEventListener('click', function(e){ if (e.target === m) m.style.display = 'none'; });
                window.addEventListener('keydown', function(e){ if (e.key === 'Escape') m.style.display = 'none'; });
            }
        })();
    </script>
    @endif
    <script>
        (function(){
            var targetForm = null;
            var modal = document.getElementById('confirmModal');
            var ok = document.getElementById('confirmOk');
            var cancel = document.getElementById('confirmCancel');
            function openConfirm(form){ targetForm = form; modal.style.display = 'flex'; }
            function closeConfirm(){ modal.style.display = 'none'; targetForm = null; }
            if (ok) ok.addEventListener('click', function(){ if(targetForm){ targetForm.submit(); closeConfirm(); } });
            if (cancel) cancel.addEventListener('click', closeConfirm);
            if (modal) modal.addEventListener('click', function(e){ if (e.target === modal) closeConfirm(); });
            window.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeConfirm(); });
            document.addEventListener('submit', function(e){
                var f = e.target;
                if (f.classList.contains('js-delete-form')) {
                    e.preventDefault();
                    openConfirm(f);
                }
            }, true);
        })();
    </script>
    @yield('additional-js')
</body>
</html>
