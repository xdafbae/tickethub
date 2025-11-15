<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - EventHub</title>
    
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

    <!-- Scripts -->
    <script src="{{ asset('js/admin-script.js') }}"></script>
    @yield('additional-js')
</body>
</html>
