<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pemeriksaan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @php use Carbon\Carbon; @endphp

    @foreach ($categories as $category => $examinations)
        <div class="page-break">
            @php
                $checkupDate = \Carbon\Carbon::parse($checkup->checkup_date);
                $tanggal = $checkupDate->translatedFormat('d F Y');

                $bulan = strtoupper($checkupDate->translatedFormat('F Y'));
                $judul =
                    $category === 'balita'
                        ? 'LAPORAN PENIMBANGAN BALITA POSYANDU'
                        : 'LAPORAN PEMERIKSAAN ' . strtoupper($category) . ' POSYANDU';

                $labelTanggal = $category === 'balita' ? 'Tanggal Penimbangan' : 'Tanggal Pemeriksaan';
            @endphp

            <div style="text-align: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">{{ $judul }}</h3>
                <h3 style="margin: 0;">BULAN {{ $bulan }}</h3>
            </div>

            <div style="margin-bottom: 15px; font-size: 12px;">
                <strong>DESA:</strong> PANDOWOHARJO<br>
                <strong>PADUKUHAN:</strong> KARANG ASEM (POSYANDU TERATAI PUTIH)<br>
                <strong>{{ $labelTanggal }}:</strong> {{ $tanggal }}
            </div>


            <table>
                <thead>
                    <tr>
                        @foreach ($columns[$category] as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($examinations as $i => $exam)
                        <tr>
                            @switch($category)
                                @case('balita')
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $exam->member->no_kk }}</td>
                                    <td>{{ $exam->member->nik }}</td>
                                    <td>{{ $exam->member->member_name }}</td>
                                    <td>{{ $exam->member->birthdate->format('Y-m-d') }}</td>
                                    <td>{{ strtoupper(substr($exam->member->gender, 0, 1)) }}</td>
                                    <td>{{ $exam->member->father }}</td>
                                    <td>{{ $exam->member->mother }}</td>
                                    <td>{{ $exam->member->nik_parent }}</td>
                                    <td>{{ $exam->member->parent_phone }}</td>
                                    <td>{{ $exam->weight }}</td>
                                    <td>{{ $exam->height }}</td>
                                    <td>{{ $exam->arm_circumference }}</td>
                                    <td>{{ $exam->head_circumference }}</td>
                                    <td>{{ $exam->weight_status }}</td>
                                @break

                                @case('anak-remaja')
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $exam->member->no_kk }}</td>
                                    <td>{{ $exam->member->nik }}</td>
                                    <td>{{ $exam->member->member_name }}</td>
                                    <td>{{ $exam->member->birthdate->format('Y-m-d') }}</td>
                                    <td>{{ strtoupper(substr($exam->member->gender, 0, 1)) }}</td>
                                    <td>{{ $exam->member->father }}</td>
                                    <td>{{ $exam->member->mother }}</td>
                                    <td>{{ $exam->member->nik_parent }}</td>
                                    <td>{{ $exam->member->parent_phone }}</td>
                                    <td>{{ $exam->weight }}</td>
                                    <td>{{ $exam->height }}</td>
                                    <td>{{ $exam->arm_circumference }}</td>
                                    <td>{{ $exam->abdominal_circumference }}</td>
                                    <td>{{ $exam->tension }}</td>
                                    <td>{{ $exam->weight_status }}</td>
                                @break

                                @case('dewasa')
                                @case('lansia')
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $exam->member->no_kk }}</td>
                                    <td>{{ $exam->member->nik }}</td>
                                    <td>{{ $exam->member->member_name }}</td>
                                    <td>{{ $exam->member->birthdate->format('Y-m-d') }}</td>
                                    <td>{{ strtoupper(substr($exam->member->gender, 0, 1)) }}</td>
                                    <td>{{ $exam->weight }}</td>
                                    <td>{{ $exam->height }}</td>
                                    <td>{{ $exam->arm_circumference }}</td>
                                    <td>{{ $exam->abdominal_circumference }}</td>
                                    <td>{{ $exam->tension }}</td>
                                    <td>{{ $exam->cholesterol }}</td>
                                    <td>{{ $exam->uric_acid }}</td>
                                    <td>{{ $exam->blood_sugar }}</td>
                                    <td>{{ $exam->weight_status }}</td>
                                @break

                                @case('ibu hamil')
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $exam->member->no_kk }}</td>
                                    <td>{{ $exam->member->nik }}</td>
                                    <td>{{ $exam->member->member_name }}</td>
                                    <td>{{ $exam->member->birthdate->format('Y-m-d') }}</td>
                                    <td>{{ strtoupper(substr($exam->member->gender, 0, 1)) }}</td>
                                    <td>{{ $exam->weight }}</td>
                                    <td>{{ $exam->height }}</td>
                                    <td>{{ $exam->arm_circumference }}</td>
                                    <td>{{ $exam->abdominal_circumference }}</td>
                                    <td>{{ $exam->tension }}</td>
                                    <td>{{ $exam->cholesterol }}</td>
                                    <td>{{ $exam->uric_acid }}</td>
                                    <td>{{ $exam->blood_sugar }}</td>
                                    <td>{{ $exam->gestational_week }}</td>
                                    <td>{{ $exam->weight_status }}</td>
                                @break
                            @endswitch
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div>
        <div style="text-align: center; margin-bottom: 10px;">
            <h3 style="margin: 0;">REKAPITULASI PEMERIKSAAN PESERTA POSYANDU </h3>
            <h3 style="margin: 0;">BULAN {{ $bulan }}</h3>
        </div>

        <p><strong>DESA:</strong> PANDOWOHARJO<br>
            <strong>PADUKUHAN:</strong> KARANG ASEM (POSYANDU TERATAI PUTIH)<br>
            <strong>Tanggal Pemeriksaan:</strong>
            {{ Carbon::parse($checkup->checkup_date)->translatedFormat('d F Y') }}
        </p>

        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jumlah Peserta</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekap as $kategori => $jumlah)
                    <tr>
                        <td>{{ ucfirst($kategori) }}</td>
                        <td>{{ $jumlah }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
