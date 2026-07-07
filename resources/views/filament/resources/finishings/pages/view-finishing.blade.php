<x-filament-panels::page>
    @php
        $finishing = $this->getRecord();
    @endphp

    <div class="mx-auto w-full max-w-3xl">
        <x-filament::section>
            <x-slot name="heading">Veredelungsdetails</x-slot>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $finishing->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Slug</p>
                    <p class="font-medium">{{ $finishing->slug ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Erstellt am</p>
                    <p class="font-medium">{{ $finishing->created_at?->format('d.m.Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Aktualisiert am</p>
                    <p class="font-medium">{{ $finishing->updated_at?->format('d.m.Y') ?? '-' }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
