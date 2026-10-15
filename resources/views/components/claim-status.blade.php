@props(['claim'])

@php
    use App\Enums\ClaimStatus;

    [$text, $classes] = match ($claim->status) {
        ClaimStatus::Requested => ['Angefragt', 'bg-line text-ink'],
        ClaimStatus::Accepted => ['Zugesagt', 'bg-red/10 text-red'],
        ClaimStatus::Sent => ['Ticket gesendet', 'bg-red text-white'],
        ClaimStatus::Received => ['Erhalten', 'bg-ink text-white'],
        ClaimStatus::Declined => ['Abgelehnt', 'bg-line text-ink/50'],
        ClaimStatus::Cancelled => ['Zurückgezogen', 'bg-line text-ink/50'],
    };
@endphp

<span {{ $attributes->class(['shrink-0 rounded-full px-3 py-1 text-xs font-bold', $classes]) }}>{{ $text }}</span>
