@props(['claim'])

@php
	use App\Enums\ClaimStatus;

	[$text, $color] = match ($claim->status) {
		ClaimStatus::Requested => ['Angefragt', 'neutral'],
		ClaimStatus::Accepted => ['Zugesagt', 'red'],
		ClaimStatus::Sent => ['Ticket gesendet', 'red'],
		ClaimStatus::Received => ['Erhalten', 'solid'],
		ClaimStatus::Declined => ['Abgelehnt', 'muted'],
		ClaimStatus::Cancelled => ['Zurückgezogen', 'muted'],
	};
@endphp

<x-badge :color="$color" {{ $attributes }}>{{ $text }}</x-badge>
