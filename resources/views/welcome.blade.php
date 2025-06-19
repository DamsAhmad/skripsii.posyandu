<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Selamat Datang di Sistem Posyandu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

    <div class="container text-center">
        <h1 class="mb-4">Selamat Datang di Sistem Monitoring Gizi Posyandu</h1>
        <p class="mb-5">Silakan klik tombol di bawah ini untuk masuk ke sistem.</p>
        <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-primary btn-lg">Masuk ke Sistem</a>
    </div>

</body>

</html>
