<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Email - EventHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Verifikasi Email</h1>
                <p>Kami telah mengirimkan link verifikasi ke email Anda.</p>
            </div>
            @if(session('status'))
                <div style="color: var(--admin-success); font-size: 13px;">{{ session('status') }}</div>
            @endif
            <form class="login-form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-block">Kirim Ulang Link Verifikasi</button>
            </form>
            <form method="POST" action="{{ route('logout') }}" style="margin-top: 10px;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-block">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>