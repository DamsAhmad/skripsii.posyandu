<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring Posyandu Karangasem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5 text-center">
        <h1 class="display-4 fw-bold">Sistem Monitoring Gizi Posyandu</h1>
        <p class="lead">Pantau perkembangan peserta posyandu Karangasem secara real-time dan terstruktur.</p>

        <img src="https://via.placeholder.com/600x300?text=Ilustrasi+Posyandu" class="img-fluid my-4"
            alt="Gambar Posyandu">

        <a href="{{ route('filament.admin.pages.dashboard') }}" class="btn btn-primary btn-lg">Mulai</a>
    </div>

    <footer class="text-center text-muted py-3">
        &copy; {{ date('Y') }} Posyandu Karangasem - Teratai Putih
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
