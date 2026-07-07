@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'invoice-document__header']) }}>
    <img
        src="{{ asset('assets/img/icons/FxxK-Logo.svg') }}"
        alt="Frau Kruner Logo"
        class="invoice-document__logo"
        height="36"
        width="123"
    >

    <div class="invoice-document__title-block">
        <h1 class="invoice-document__title">{{ $title }}</h1>

        @if ($subtitle)
            <p class="invoice-document__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</div>
