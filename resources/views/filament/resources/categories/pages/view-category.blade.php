<x-filament-panels::page>
    @php
        $category = $this->getRecord();
    @endphp

    <div class="mx-auto w-full max-w-4xl space-y-6">
        <x-filament::section>
            <x-slot name="heading">Kategorie Details</x-slot>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Bestellung</p>
                    <p class="font-medium">{{ $category->order ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Auf der Startseite?</p>
                    <p class="font-medium">{{ (int) ($category->featured ?? 0) === 1 ? 'Ja' : 'Nein' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $category->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Slug</p>
                    <p class="font-medium">{{ $category->slug ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Titel</p>
                    <p class="font-medium">{{ $category->title ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Hintergrundfarbe</p>
                    <p class="font-medium">{{ $category->color ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Schriftfarbe</p>
                    <p class="font-medium">{{ $category->font ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-sm text-gray-500">Beschreibung</p>
                <p class="font-medium">{{ $category->description ?: '-' }}</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Bild</x-slot>

            @if (! empty($category->image))
                <img src="{{ asset('storage/' . ltrim($category->image, '/')) }}" alt="{{ $category->name }}" class="max-h-80 rounded-lg border border-gray-200 object-contain" />
            @else
                <p class="text-sm text-gray-500">Kein Bild vorhanden.</p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
