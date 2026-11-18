
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Keluhan') | Kost.io</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @php
        // CSS khusus UC-08
        $cssPath = public_path('assets/kostio/keluhan.css');

        $cssVersion = is_file($cssPath)
            ? filemtime($cssPath)
            : 1;
    @endphp

    <link
        rel="stylesheet"
        href="{{ asset('assets/kostio/keluhan.css') }}?v={{ $cssVersion }}"
    >

    <script
        src="{{ asset('assets/kostio/keluhan.js') }}"
        defer
    ></script>
    @include('layouts.partials.kostio-ui-assets')
</head>

<body class="kio-ready kio-resident">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])

<div class="dashboard">

    {{-- SIDEBAR PENGHUNI --}}
    <aside class="sidebar kio-sidebar">
            @yield('kioResidentSidebar')
        </aside>

    {{-- AREA UTAMA --}}
    <div class="main-panel">

        {{-- HEADER --}}
        <header class="topbar kio-header">
            @yield('kioResidentHeader')
        </header>

        {{-- KONTEN HALAMAN --}}
        <main class="page-content">

            <div class="breadcrumb">
                BERANDA / MENGAJUKAN KELUHAN / KERUSAKAN
            </div>

            <div class="page-heading">

                <h1>
                    @yield(
                        'heading',
                        'Ajukan keluhan / kerusakan'
                    )
                </h1>

                <span class="tag tag-brand">
                    Kost.io
                </span>

            </div>

            <p class="page-lead">
                @yield(
                    'subtitle',
                    'Sampaikan kendala agar pemilik dapat segera menindaklanjuti.'
                )
            </p>

            <div class="content-grid">

                {{-- FORM / ISI UC-08 --}}
                <section class="main-card">
                    @yield('content')
                </section>

                {{-- INFORMASI KOST --}}
                @include('keluhan.partials.hunian')

            </div>

        </main>

    </div>

</div>

</body>
</html>
