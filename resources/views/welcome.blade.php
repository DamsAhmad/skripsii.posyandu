<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>SIM-Gizi Posyandu Teratai Putih Karangasem</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Custom Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@100;200;300;400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* Custom CSS untuk layout */
        .profile-container {
            position: relative;
            max-width: 100%;
        }

        .profile {
            position: relative;
            z-index: 1;
            background: none !important;
            /* Pastikan background benar-benar tidak ada */
        }

        .profile-img {
            position: relative;
            z-index: 2;
            max-width: 100%;
            height: auto;
            border-radius: 0;
            /* Hilangkan rounded corners */
            box-shadow: none !important;
            /* Pastikan tidak ada shadow */
        }

        /* Hilangkan semua dots jika tidak diperlukan */
        .dots-1,
        .dots-2,
        .dots-3,
        .dots-4 {
            display: none;
            /* Nonaktifkan semua dots */
        }

        /* Tambahkan margin bawah untuk judul Posyandu */
        .posyandu-title {
            margin-bottom: 2.5rem !important;
            /* Jarak yang lebih longgar */
        }

        @media (max-width: 991.98px) {
            .header-content {
                text-align: center;
                margin-bottom: 2rem;
            }

            .profile-container {
                margin: 0 auto;
            }
        }
    </style>
</head>

<body class="d-flex flex-column h-100">
    <main class="flex-shrink-0">
        <!-- Header-->
        <header class="py-5">
            <div class="container px-5 pb-5">
                <div class="row gx-5 align-items-center">
                    <!-- Kolom teks (kiri) -->
                    <div class="col-lg-6 col-xxl-5">
                        <div class="header-content text-start">
                            <div style="margin-bottom: 12px;">
                                <img src="{{ asset('img/logo.svg') }}" alt="Sistem Monitoring Gizi"
                                    style="max-width: 100px; height: auto; object-fit: contain;" />
                            </div>
                            <div class="badge bg-gradient-primary-to-secondary text-white mb-4">
                                <div class="text-uppercase">Sehat &middot; Akurat &middot; Sejahtera</div>
                            </div>
                            <div class="fs-3 fw-light text-muted">Selamat Datang di</div>
                            <h1 class="display-3 fw-bolder mb-2">
                                <span class="text-gradient d-block">Sistem Monitoring Gizi</span>
                            </h1>
                            <h2 class="fw-bold mb-4 posyandu-title" style="color: #555;">Posyandu Teratai Putih
                                Karangasem</h2>
                            <div class="d-grid gap-3 d-sm-flex justify-content-sm-start">
                                <a class="btn btn-primary btn-lg px-5 py-3 me-sm-3 fs-6 fw-bolder"
                                    href="{{ route('filament.admin.auth.login') }}">Mulai Sekarang!</a>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom gambar (kanan) - Versi bersih tanpa efek -->
                    <div class="col-lg-6 col-xxl-7">
                        <div class="profile-container">
                            <div class="profile">
                                <img class="profile-img" src="{{ asset('img/ilustrasi1.png') }}"
                                    alt="Sistem Monitoring Gizi" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- About Section-->
        <section class="bg-light py-5">
            <div class="container px-5">
                <div class="row gx-5 justify-content-center">
                    <div class="col-xxl-8">
                        <div class="text-center my-5">
                            <h2 class="display-5 fw-bolder"><span class="text-gradient d-inline">🌸 Untuk Tumbuh yang
                                    Lebih Baik 🌸</span></h2>
                            <p class="text-muted">Kami percaya bahwa tumbuh kembang yang baik dimulai dari data yang
                                akurat.
                                Sistem ini hadir untuk mendukung Posyandu Teratai Putih dalam menjaga kesehatan dan gizi
                                masyarakat secara berkelanjutan. Melalui platform ini, setiap angka yang terekam adalah
                                langkah menuju
                                tumbuh kembang yang optimal bagi anak-anak dan keluarga di sekitar kita.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
