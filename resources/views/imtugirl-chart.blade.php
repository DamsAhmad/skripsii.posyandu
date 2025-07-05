<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grafik IMT/U - {{ $member->member_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
            margin: 0;
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

        .chart-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <a href="{{ url('/admin/DataPeserta/' . $member->id) }}" class="btn-back">
        ← Kembali ke Profil
    </a>

    <div class="chart-container">
        <canvas id="imtuGirlChart" style="width: 100%; height: 70vh;" data-member-name="{{ $member->member_name }}"
            data-points='@json($dataPoints)' data-who-curves='@json($whoCurves)'></canvas>

    </div>

    <script>
        window.IMTUChartConfig = {
            memberName: "{{ $member->member_name }}",
            dataPoints: @json($dataPoints),
            whoCurves: @json($whoCurves)
        };
    </script>

    <script src="{{ asset('js/filament/imtu-growth_girl-chart.js') }}"></script>
</body>

</html>
