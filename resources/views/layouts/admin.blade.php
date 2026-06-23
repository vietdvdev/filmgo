<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', 'Tổng Quan Hệ Thống - FilmGo')</title>
    @stack('head')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary-fixed-dim": "#b7c4ff",
                        "on-surface": "#191c1e",
                        "on-tertiary-fixed-variant": "#7e2c00",
                        "background": "#f8f9fb",
                        "tertiary-fixed-dim": "#ffb598",
                        "tertiary": "#a03a00",
                        "inverse-on-surface": "#eff1f3",
                        "on-primary": "#ffffff",
                        "outline": "#737687",
                        "primary-fixed": "#dce1ff",
                        "secondary-fixed-dim": "#b8c8d8",
                        "surface-container-highest": "#e0e3e5",
                        "on-tertiary-container": "#fffaff",
                        "on-error": "#ffffff",
                        "surface-variant": "#e0e3e5",
                        "on-error-container": "#93000a",
                        "on-background": "#191c1e",
                        "on-secondary-fixed": "#0d1d29",
                        "primary": "#004be3",
                        "secondary": "#50606e",
                        "surface-container-low": "#f2f4f6",
                        "surface-container": "#eceef0",
                        "inverse-surface": "#2d3133",
                        "on-tertiary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "on-surface-variant": "#434655",
                        "on-primary-fixed-variant": "#0039b4",
                        "on-primary-container": "#fdfbff",
                        "on-primary-fixed": "#001551",
                        "error": "#ba1a1a",
                        "tertiary-fixed": "#ffdbce",
                        "surface-tint": "#054de9",
                        "surface-dim": "#d8dadc",
                        "primary-container": "#3366ff",
                        "on-secondary-container": "#566674",
                        "surface-bright": "#f8f9fb",
                        "secondary-fixed": "#d4e5f5",
                        "secondary-container": "#d4e5f5",
                        "tertiary-container": "#c94b00",
                        "on-tertiary-fixed": "#370e00",
                        "on-secondary-fixed-variant": "#394955",
                        "surface": "#f8f9fb",
                        "surface-container-high": "#e6e8ea",
                        "error-container": "#ffdad6",
                        "inverse-primary": "#b7c4ff",
                        "outline-variant": "#c3c5d8",
                        "on-secondary": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "stack-xl": "40px",
                        "stack-md": "16px",
                        "container-max": "1440px",
                        "stack-lg": "24px",
                        "gutter": "24px",
                        "stack-xs": "4px",
                        "margin-page": "40px",
                        "stack-sm": "8px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "headline-lg": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "body-md": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-sm": ["18px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "32px", "fontWeight": "700" }],
                        "body-md": ["14px", { "lineHeight": "22px", "fontWeight": "400" }],
                        "label-md": ["13px", { "lineHeight": "18px", "letterSpacing": "0.05em", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        /* Ambient Shadow for Level 1 */
        .shadow-ambient-sm { box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.03); }
        .chart-gradient { fill: url(#chartGrad); }
    </style>
</head>
<body class="bg-background text-on-surface h-screen overflow-hidden flex antialiased">

    <!-- Shared Component: SideNavBar -->
    @include('layouts.partials.sidebar')

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col pl-[280px]">
        <!-- Shared Component: TopNavBar -->
        @include('layouts.partials.header')

        <!-- Scrollable Canvas -->
        @yield('content')
    </div>

</body>
@stack('scripts')
</html>
