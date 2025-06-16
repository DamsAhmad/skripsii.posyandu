<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <canvas id="bbuChart" width="600" height="400" style="max-width: 100%; height: auto;"
        data-member-name="{{ $member->member_name }}" data-weights='@json($dataPoints)'></canvas>
</body>

</html>



{{-- CONTOH HARDCODE BAWAH IKI --}}
{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grafik BB/U</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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


    <h2 style="font-size: 20px; font-weight: bold;">Grafik KMS: Berat Badan Berdasarkan Usia (BB/U)</h2>
    <canvas id="growthChart" width="800" height="400"></canvas>

    <script>
        async function drawChart() {
            const ages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

            const chartData = {
                '-3': [2.1, 2.8, 3.4, 3.9, 4.3, 4.6, 4.9, 5.1, 5.3, 5.5],
                '-2': [2.7, 3.4, 4.0, 4.5, 5.0, 5.4, 5.7, 6.0, 6.3, 6.5],
                '+2': [4.9, 5.8, 6.6, 7.3, 7.9, 8.4, 8.9, 9.3, 9.6, 9.9]
            };

            const dataAnak = [3.0, 3.8, 4.5, 5.0, 5.6, 6.1, 6.5, 7.0, 7.2, 7.5];

            const datasets = [{
                    label: 'Z-score -3',
                    data: chartData['-3'],
                    borderColor: 'transparent',
                    fill: {
                        target: 'origin',
                        above: 'rgba(255, 0, 0, 0.2)',
                    },
                    pointRadius: 0,
                },
                {
                    label: 'Z-score -2',
                    data: chartData['-2'],
                    borderColor: 'transparent',
                    fill: {
                        target: '-3',
                        above: 'rgba(255, 255, 0, 0.2)',
                    },
                    pointRadius: 0,
                },
                {
                    label: 'Z-score +2',
                    data: chartData['+2'],
                    borderColor: 'transparent',
                    fill: {
                        target: '-2',
                        above: 'rgba(0, 255, 0, 0.2)',
                    },
                    pointRadius: 0,
                },
                {
                    label: 'Data Anak',
                    data: dataAnak,
                    borderColor: 'blue',
                    backgroundColor: 'blue',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 5,
                }
            ];

            const ctx = document.getElementById('growthChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ages,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        },
                        title: {
                            display: true,
                            text: 'Grafik KMS: Berat Badan Berdasarkan Usia (BB/U)',
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Usia (bulan)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Berat Badan (kg)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        window.addEventListener('DOMContentLoaded', drawChart);
    </script>
</body>

</html> --}}
