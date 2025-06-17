<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/filament/imtu-growth_boy-chart.js') }}"></script>
    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 0.375rem;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <a href="{{ url('/admin/DataPeserta/' . $member->id) }}" class="btn-back">
        ← Kembali ke Profil
    </a>
    <canvas id="imtuBoyChart" width="600" height="400" style="max-width: 100%; height: auto;"
        data-member-name="{{ $member->member_name }}" data-points='@json($dataPoints)'></canvas>
</body>

</html>
