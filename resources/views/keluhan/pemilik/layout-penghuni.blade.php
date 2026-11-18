
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title', 'Status Keluhan Saya') | Kost.io
    </title>

    <link rel="stylesheet"
          href="{{ asset('assets/kostio/uc14.css') }}">
    @include('layouts.partials.kostio-ui-assets')
</head>

<body class="owner14 kio-ready kio-resident">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])

<div class="owner14-shell">

    <aside class="owner14-sidebar kio-sidebar">
            @yield('kioResidentSidebar')
        </aside>

    <div class="owner14-workarea">

        <header class="owner14-topbar kio-header">
            @yield('kioResidentHeader')
        </header>

        <main class="owner14-content">

            <div class="owner14-breadcrumb">
                BERANDA / MELIHAT &amp; MENANGANI KELUHAN
            </div>

            <div class="owner14-heading">

                <h1>
                    @yield('heading', 'Status keluhan saya')
                </h1>

                <span class="owner14-kost-pill">
                    Kost.io
                </span>

            </div>

            <p class="owner14-subtitle">
                Tinjau laporan dan bantu penghuni menyelesaikan kendalanya.
            </p>

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
