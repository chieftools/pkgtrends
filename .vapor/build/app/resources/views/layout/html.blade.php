@if(!empty($title) && !is_array($title))
    @php($title = [$title])
@endif
<!DOCTYPE html>
<html class="app" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google" value="notranslate">

    @stack('head.meta')

    @php($title = isset($title) ? implode(' - ', array_map('strip_tags', $title)) . ' - ' . config('app.name') : config('app.title'))
    <title>{{ $title }}</title>

    <meta name="description" content="{{ config('app.description') }}"/>
    <meta property="og:title" content="{{ $title }}"/>
    <meta property="og:description" content="{{ config('app.description') }}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ config('app.url') }}"/>
    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:title" content="{{ $title }}"/>
    <meta name="twitter:creator" content="@stayallive"/>
    <meta name="twitter:description" content="{{ config('app.description') }}"/>

    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('img/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('img/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('img/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('img/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('img/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('img/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon/favicon-16x16.png') }}">

    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('build/app.css') }}">
    <script src="https://kit.fontawesome.com/c8291c8701.js" crossorigin="anonymous"></script>
    @stack('head.style')
</head>
<body class="{{ $bodyClass or '' }}">
    @yield('body')

    @stack('body.before_script')
    <script src="{{ mix('build/app.js') }}"></script>
    @stack('body.script')

    @include('partial.analytics')
</body>
</html>
