{{-- Kost.io UI system | berbagi tipografi, navigasi, dan token visual antar UC --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/kostio/kostio-unified.css') }}?v={{ @filemtime(public_path('assets/kostio/kostio-unified.css')) ?: 1 }}">
<script src="{{ asset('assets/kostio/kostio-unified.js') }}?v={{ @filemtime(public_path('assets/kostio/kostio-unified.js')) ?: 1 }}" defer></script>

<link rel="stylesheet" href="{{ asset('assets/kostio/ruang-pemilik-halaman.css') }}?v={{ @filemtime(public_path('assets/kostio/ruang-pemilik-halaman.css')) ?: 1 }}">
