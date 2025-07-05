<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>Grafik BB/U - {{ $member->member_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="{{ asset('js/filament/growth-chart.js') }}"></script>
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

    <div id="rotateWarning" style="text-align:center; margin-top:1rem; display:none; font-weight:bold; color:red;">
        Putar perangkat ke mode landscape untuk melihat grafik lebih nyaman 📱↔️
    </div>
    @php
        $safeJson = json_encode($dataPoints, JSON_HEX_APOS | JSON_HEX_QUOT);
    @endphp

    <canvas id="bbuboyChart" data-member-name="{{ $member->member_name }}" data-weights='@json($dataPoints)'
        data-who-curves='@json($whoCurves)'></canvas>


    <script>
        function showOrientationWarning() {
            const warning = document.getElementById('rotateWarning');
            if (window.innerWidth < window.innerHeight) {
                warning.style.display = 'block'; // Portrait
            } else {
                warning.style.display = 'none'; // Landscape
            }
        }

        window.addEventListener('load', showOrientationWarning);
        window.addEventListener('resize', showOrientationWarning);
        window.addEventListener('orientationchange', showOrientationWarning);
    </script>
</body>

</html>
