<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - EventHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Masuk</h1>
                <p>Selamat datang kembali di EventHub</p>
            </div>
            <form class="login-form" method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:8px;">
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                    <a href="{{ route('register') }}">Buat akun</a>
                </div>
            </form>
        </div>
        <div class="login-footer">
            <p>Dengan masuk, Anda menyetujui ketentuan layanan.</p>
        </div>
    </div>
    <script src="{{ asset('js/admin-script.js') }}"></script>
</body>
</html>