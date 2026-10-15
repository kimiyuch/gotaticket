{{-- FC Winterthur crest. Decorative by default; pass a label to expose it. --}}
@props(['label' => null])

<span {{ $attributes->merge(['class' => 'inline-flex [&>svg]:size-full']) }} @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
	{!! file_get_contents(resource_path('svg/fcw.svg')) !!}
</span>
