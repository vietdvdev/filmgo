<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FilmGo - Đặt Vé Xem Phim Nhanh Chóng')</title>
    
    <!-- Tailwinds CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Google Fonts & Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: "#E50914", /* Đỏ chuẩn điện ảnh */
                            secondary: "#1A1A1A",
                            accent: "#E50914",
                            dark: "#0F0F0F",
                            light: "#F5F5F7"
                        }
                    },
                    fontFamily: {
                        sans: ["Outfit", "sans-serif"]
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0F0F0F;
            color: #FFFFFF;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
    @yield('styles')
</head>
<body class="bg-brand-dark text-white min-h-screen flex flex-col antialiased">

    <!-- Header / Navigation -->
    @include('partials.header')

    <!-- Main Content Wrapper -->
    <main class="flex-grow">
        {{-- Banner cảnh báo khi bị chặn từ khu vực quản trị (HTTP 403) --}}
        @if(session('forbidden_error'))
        <div class="bg-red-900/80 border-b border-red-700 px-6 py-3 flex items-center gap-3">
            <span class="material-symbols-outlined text-red-400 text-xl shrink-0">block</span>
            <p class="text-sm text-red-200 font-medium">{{ session('forbidden_error') }}</p>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Scripts -->
    @yield('scripts')
</body>
</html>
