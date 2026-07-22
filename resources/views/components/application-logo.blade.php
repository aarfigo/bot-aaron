@php
	// Prefer the new image if present (logo daniel.png), otherwise fall back to auth-logo.svg if present.
	$preferred = public_path('img/logo daniel.png');
	$fallback = public_path('img/auth-logo.svg');
	if (file_exists($preferred)) {
		$logoUrl = asset('img/logo daniel.png');
	} elseif (file_exists($fallback)) {
		$logoUrl = asset('img/auth-logo.svg');
	} else {
		$logoUrl = null;
	}
@endphp

@if($logoUrl)
	<img src="{{ $logoUrl }}" {{ $attributes }} alt="{{ config('app.name', 'Laravel') }}" class="object-contain" />
@else
	<div class="fw-bold">{{ config('app.name', 'Laravel') }}</div>
@endif
