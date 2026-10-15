@props(['title' => null, 'description' => null])

@php
	$siteName = config('app.name');
	$fullTitle = $title ? $title.' · '.$siteName : $siteName.' – Tickets für FCW-Heimspiele, von Fan zu Fan';
	$description ??= match (true) {
		request()->routeIs('offers.index') => 'Freie Plätze für die nächsten Heimspiele des FC Winterthur, weitergegeben von Fans. Kostenlos oder zum Originalpreis.',
		default => 'Du kannst an einem FCW-Heimspiel nicht? Gib deinen Platz an einen Fan weiter, der ein Ticket sucht. Kostenlos, ohne Zwischenhandel, direkt von Fan zu Fan.',
	};
	// Only the public pages belong in search results; accounts, auth and admin stay out.
	$indexable = request()->routeIs('home', 'offers.index');
@endphp

{{-- Title, description, canonical URL and link previews (Open Graph, X). The image comes from resources/views/og-image.blade.php, see scripts/og-image.sh. --}}
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $indexable ? 'index, follow' : 'noindex, nofollow' }}">
@if ($indexable)
	<link rel="canonical" href="{{ url()->current() }}">
@endif
<meta property="og:type" content="website">
<meta property="og:locale" content="de_CH">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('images/og.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Ein gezeichnetes Ticket neben dem Satz: Du chasch nöd? Öpper anders chunnt gern.">
<meta name="twitter:card" content="summary_large_image">
