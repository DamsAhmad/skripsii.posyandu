<!DOCTYPE html>
<html>

<head>
    <title>Data Pemeriksaan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Data Pemeriksaan - Sesi {{ $checkup->id }}</h2>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>JK</th>
                <th>Kategori</th>
                <th>Usia</th>
                <th>BB</th>
                <th>TB</th>
                <th>Status Gizi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($examinations as $exam)
                <tr>
                    <td>{{ $exam->member->member_name }}</td>
                    <td>{{ $exam->member->gender }}</td>
                    <td>{{ $exam->member->category }}</td>
                    <td>{{ \Carbon\Carbon::parse($exam->member->birthdate)->age }} tahun</td>
                    <td>{{ $exam->weight }}</td>
                    <td>{{ $exam->height }}</td>
                    <td>{{ $exam->weight_status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
