<x-filament-panels::page>
    @php
        $tag = $this->getRecord();
    @endphp

    <div class="mx-auto w-full max-w-3xl">
        <x-filament::section>
            <x-slot name="heading">Tag Details</x-slot>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $tag->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Slug</p>
                    <p class="font-medium">{{ $tag->slug ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Erstellt am</p>
                    <p class="font-medium">{{ $tag->created_at?->format('Y-m-d H:i:s') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Aktualisiert am</p>
                    <p class="font-medium">{{ $tag->updated_at?->format('Y-m-d H:i:s') ?? '-' }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
