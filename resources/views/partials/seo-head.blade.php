@php
    $mergedSeo = array_merge($defaultSeo ?? [], $routeSeo ?? [], $seo ?? []);
    $pageTitle = $pageTitle ?? ($mergedSeo['title'] ?? ($siteTitle ?? 'GrinMuzik'));
    $siteMetaTitle = $siteTitle ?? 'GrinMuzik';

    if ($pageTitle !== $siteMetaTitle && ! str_contains($pageTitle, $siteMetaTitle)) {
        $pageTitle .= ' | ' . $siteMetaTitle;
    }

    $metaDescription = $mergedSeo['description'] ?? 'Discover independent music, playlists, charts, and artists on GrinMuzik.';
    $canonicalUrl = $mergedSeo['canonical'] ?? url()->current();
    $robots = $mergedSeo['robots'] ?? 'index,follow';
    $ogType = $mergedSeo['type'] ?? 'website';
    $ogTitle = $mergedSeo['og_title'] ?? $pageTitle;
    $ogDescription = $mergedSeo['og_description'] ?? $metaDescription;
    $ogUrl = $mergedSeo['og_url'] ?? $canonicalUrl;
    $ogImage = $mergedSeo['image'] ?? null;
    $twitterCard = $mergedSeo['twitter_card'] ?? ($ogImage ? 'summary_large_image' : 'summary');
    $siteNameTag = $mergedSeo['site_name'] ?? ($siteName ?? 'GrinMuzik');

    $jsonLd = $mergedSeo['json_ld'] ?? [];

    if ($jsonLd && array_key_exists('@context', $jsonLd)) {
        $jsonLd = [$jsonLd];
    }

    $schemaBlocks = array_values(array_filter(array_merge(
        [$organizationSchema ?? null, $websiteSchema ?? null],
        is_array($jsonLd) ? $jsonLd : []
    )));
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta name="theme-color" content="#cc181e">

<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:site_name" content="{{ $siteNameTag }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

@foreach($schemaBlocks as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endforeach
