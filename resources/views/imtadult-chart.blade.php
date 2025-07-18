<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grafik IMT Dewasa - {{ $member->member_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script
        src="{{ asset('js/filament/imt-adult-chart.js') }}?v={{ filemtime(public_path('js/filament/imt-adult-chart.js')) }}">
    </script>
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
    <canvas id="imtAdultChart" data-member-name="{{ $member->member_name }}" data-category="{{ $category }}"
        data-points='@json($dataPoints)'>
    </canvas>
    <script>
        console.log("IMT Data JSON:", @json($dataPoints));
    </script>
</body>

</html>
