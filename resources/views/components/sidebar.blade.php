<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-logo">
            🎟️ EventHub
        </a>
    </div>

    <nav class="sidebar-nav">
        <li>
            <a href="#" 
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="#" 
               class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                <span class="nav-icon">🎪</span>
                <span class="nav-label">Events</span>
            </a>
        </li>
        <li>
            <a href="#" 
               class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                <span class="nav-icon">📈</span>
                <span class="nav-label">Analytics</span>
            </a>
        </li>
        <li>
            <a href="#" 
               class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Settings</span>
            </a>
        </li>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"></div>
            <div class="user-details">
                <p class="user-name">Dafa</p>
                <p class="user-role">Admin</p>
            </div>
        </div>
    </div>
</aside>
