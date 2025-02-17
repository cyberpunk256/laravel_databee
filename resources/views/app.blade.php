<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @inertiaHead
    </head>
    <body>
        @inertia
        @vite([
            'resources/js/app.js',
            'resources/scss/app.scss',
        ])
    </body>
</html>
