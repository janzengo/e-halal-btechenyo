<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Force light mode globally --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }
            html.dark, body.dark {
                color-scheme: light !important;
            }
        </style>
        <script>
            // Force light mode on page load
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            if (document.body) {
                document.body.classList.remove('dark');
                document.body.classList.add('light');
            }
        </script>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/images/logos/logo.png" sizes="any">
        <link rel="icon" href="/images/logos/logo.png" type="image/png">
        <link rel="apple-touch-icon" href="/images/logos/logo.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
