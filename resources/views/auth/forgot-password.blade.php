<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - EventHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Lupa Password</h1>
                <p>Masukkan email untuk menerima link reset</p>
            </div>
            @if(session('status'))
                <div style="color: var(--admin-success); font-size: 13px;">{{ session('status') }}</div>
            @endif
            <form class="login-form" method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block">Kirim Link Reset</button>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:8px;">
                    <a href="{{ route('login') }}">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/admin-script.js') }}"></script>
</body>
</html>