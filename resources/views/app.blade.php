{{--
    Modèle Blade racine de l'application (vue "app").

    C'est la seule page HTML réellement renvoyée par Laravel : elle est chargée
    à la première visite (voir HandleInertiaRequests::$rootView). Ensuite,
    Inertia et React remplacent son contenu sans recharger la page.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        {{-- Précharge les polices déclarées pour le site. --}}
        @fonts

        {{-- Active le rechargement à chaud de React en développement, puis charge
             le CSS, le point d'entrée JavaScript et le composant de la page demandée. --}}
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        {{-- Point d'ancrage où Inertia affiche la page React courante. --}}
        <x-inertia::app />
    </body>
</html>
