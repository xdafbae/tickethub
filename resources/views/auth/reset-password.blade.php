<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - EventHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Reset Password</h1>
                <p>Masukkan password baru untuk akun Anda</p>
            </div>
            <form class="login-form" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ $email ?? old('email') }}" required>
                    @error('email')<span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password" required>
                    @error('password')<span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:8px;">
                    <a href="{{ route('login') }}">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/admin-script.js') }}"></script>
</body>
</html>