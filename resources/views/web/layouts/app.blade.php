<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $seoTitle = trim($__env->yieldContent('title', config('app.name', 'VertiCode') . ' - Track Your Problem Solving Journey'));
        $seoDescription = trim($__env->yieldContent('description', 'VertiCode helps competitive programmers track progress, compare rankings, and grow with community insights.'));
        $seoKeywords = trim($__env->yieldContent('keywords', 'competitive programming, coding challenges, problem-solving, contests, leaderboard, community'));
        $seoRobots = trim($__env->yieldContent('robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
        $seoCanonical = trim($__env->yieldContent('canonical', url()->current()));
        $seoType = trim($__env->yieldContent('og_type', 'website'));
        $seoImage = trim($__env->yieldContent('image', asset('favicon.ico')));
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <meta name="author" content="VertiCode Team">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:site_name" content="{{ config('app.name', 'VertiCode') }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('web/img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('web/img/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('web/img/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>{{ $seoTitle }}</title>
    <link rel="stylesheet" href="{{ asset('web/css/style-web.css') }}?v={{ time() }}">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name', 'VertiCode'),
            'url' => rtrim(config('app.url'), '/'),
            'description' => $seoDescription,
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('leaderboard') . '?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @stack('structured-data')
    @stack('styles')
</head>

<body>
    @include('web.layouts.includes.navbar')

    @yield('content')

    @include('web.layouts.includes.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
