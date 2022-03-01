@if(!empty($title) && !is_array($title))
    @php($title = [$title])
@endif
<!DOCTYPE html>
<html class="app" dir="ltr" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('head.meta')

    <link rel="preconnect" href="{{ static_asset() }}">

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

    <link rel="icon" href="{{ static_asset('icons/pkgtrends_favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ static_asset('icons/pkgtrends_favicon.ico') }}" sizes="32x32">

    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('build/app.css') }}">
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
