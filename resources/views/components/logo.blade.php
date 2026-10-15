@props(['horizontal' => false])

<span {{ $attributes->merge(['class' => 'inline-flex [&>svg]:h-full [&>svg]:w-auto']) }} aria-hidden="true">
    {!! file_get_contents(resource_path($horizontal ? 'svg/ticket-horizontal.svg' : 'svg/ticket.svg')) !!}
</span>
