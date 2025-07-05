<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grafik LiLA Ibu Hamil - {{ $member->member_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
        }

        .chart-container {
            width: 100%;
            max-width: 1200px;
            height: 600px;
            /* TAMBAH TINGGI-NYA */
            margin: 0 auto;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
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

    <a href="{{ url('/admin/DataPeserta/' . $member->id) }}" class="btn-back">← Kembali ke Profil</a>

    <div class="chart-container">
        <canvas id="pregnantChart" data-member-name="{{ $member->member_name }}"
            data-points='@json($dataPoints)'></canvas>
    </div>

    <script>
        window.PregnantChartConfig = {
            memberName: "{{ $member->member_name }}",
            dataPoints: @json($dataPoints),
        };

        console.log("CEK DATA POINT", window.PregnantChartConfig.dataPoints);
    </script>

    <script src="{{ asset('js/filament/pregnant-chart.js') }}"></script>

</body>

</html>
