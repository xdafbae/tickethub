<header class="admin-header">
    <div>
        <h2>@yield('page-title', 'Dashboard')</h2>
        @hasSection('page-subtitle')
            <p style="margin: 4px 0 0 0; color: var(--admin-muted); font-size: 13px;">@yield('page-subtitle')</p>
        @endif
    </div>
    <div class="admin-header-actions">
        @yield('header-actions')
        <form action="#" method="POST">
           
            @csrf
            <button type="submit" class="btn btn-secondary">Logout</button>
        </form>
    </div>
</header>
