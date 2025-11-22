<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-logo">
            🎟️ EventHub
        </a>
    </div>

    <nav class="sidebar-nav">
        <li>
            <a href="{{ route('admin.dashboard') }}" 
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>

        @role('admin')
        <li>
            <a href="{{ route('admin.events.index') }}" 
               class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                <span class="nav-icon">🎪</span>
                <span class="nav-label">Events</span>
            </a>
        </li>
        @endrole

        @role('admin')
        <li>
            <a href="{{ route('admin.ticket_types.index') }}" 
               class="{{ request()->routeIs('admin.ticket_types.*') ? 'active' : '' }}">
                <span class="nav-icon">🎫</span>
                <span class="nav-label">Ticket Types</span>
            </a>
        </li>
        @endrole

        @hasanyrole('admin|gate_staff')
        <li>
            <a href="{{ route('admin.gate.scanner') }}"
               class="{{ request()->routeIs('admin.gate.scanner') ? 'active' : '' }}">
                <span class="nav-icon">📷</span>
                <span class="nav-label">Gate Scanner</span>
            </a>
        </li>
        @endhasanyrole

        @role('admin')
        <li>
            <a href="{{ route('admin.promos.index') }}"
               class="{{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">
                <span class="nav-icon">🏷️</span>
                <span class="nav-label">Promos</span>
            </a>
        </li>
        @endrole

        @role('admin')
        <li>
            <a href="{{ route('admin.orders.index') }}"
               class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <span class="nav-icon">🧾</span>
                <span class="nav-label">Orders</span>
            </a>
        </li>
        @endrole

        @role('admin')
        <li>
            <a href="#" 
               class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                <span class="nav-icon">📈</span>
                <span class="nav-label">Analytics</span>
            </a>
        </li>
        @endrole

        @role('admin')
        <li>
            <a href="#" 
               class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Settings</span>
            </a>
        </li>
        @endrole
    </nav>
</aside>
