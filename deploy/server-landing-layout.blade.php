@props([
    'company' => null,
    'companyName' => 'AutoSpa',
    'locality' => 'Kimana',
    'title' => null,
    'description' => null,
    'jsonLd' => null,
    'phone' => null,
    'email' => null,
    'address' => null,
    'ogImage' => null,
])

@php
    $pageTitle = $title ?? ($companyName.' — Auto Spa in '.$locality);
    $pageDescription = $description ?? ('Book Auto Spa detailing, wash, or carpet cleaning in '.$locality.'. '.$companyName.' — spotless finish, on-time slots.');
    $canonical = url('/');
    $ogImage = $ogImage
        ?? (filled($company?->logo_path) ? asset('storage/'.$company->logo_path) : asset('brand/logo.jpeg'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_KE">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:site_name" content="{{ $companyName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/jpeg" href="{{ asset('brand/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($jsonLd)
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
</head>
<body class="landing-body antialiased">
    {{ $slot }}
</body>
</html>
