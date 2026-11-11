<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            html {
                background-color: #f3f0e8;
            }
        </style>

        <meta name="theme-color" content="#201f1c">
        <link rel="icon" href="/brand/kotagede-jewellery.png" type="image/png" sizes="any">
        <link rel="apple-touch-icon" href="/brand/kotagede-jewellery.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
