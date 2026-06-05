<x-filament-panels::page>
    @php
        $user = $this->getRecord();
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <x-filament::section>
            <x-slot name="heading">User Details</x-slot>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-sm text-gray-500">Nutzername</p>
                    <p class="font-medium">{{ $user->username ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Vorname</p>
                    <p class="font-medium">{{ $user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nachname</p>
                    <p class="font-medium">{{ $user->last_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">E-Mail</p>
                    <p class="font-medium">{{ $user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nutzerkategorie</p>
                    <p class="font-medium">{{ $user->role?->display_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Verifiziert</p>
                    <p class="font-medium">{{ (int) ($user->verified ?? 0) === 1 ? 'Ja' : 'Nein' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="font-medium">{{ (int) ($user->status ?? 0) === 1 ? 'Aktiv' : 'Pausiert' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Gepusht</p>
                    <p class="font-medium">{{ (int) ($user->boosted ?? 0) === 1 ? 'Ja' : 'Nein' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Erstellt am</p>
                    <p class="font-medium">{{ $user->created_at?->format('Y-m-d H:i:s') ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-sm text-gray-500">Profilbeschreibung</p>
                <p class="font-medium">{{ $user->profile?->description ?: '-' }}</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
