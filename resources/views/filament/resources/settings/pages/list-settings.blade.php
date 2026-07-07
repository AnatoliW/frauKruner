<x-filament-panels::page>
    <style>
        .settings-page {
            display: grid;
            gap: 1.5rem;
        }

        .settings-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .settings-row {
            display: grid;
            gap: 1rem;
            border-bottom: 1px solid rgb(229 231 235);
            padding-bottom: 1.75rem;
        }

        .dark .settings-row {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .settings-row-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .settings-label {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: rgb(17 24 39);
        }

        .dark .settings-label {
            color: rgb(255 255 255);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 248px;
            gap: 1rem;
            align-items: start;
        }

        .settings-current-value {
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: rgb(107 114 128);
        }

        .dark .settings-current-value {
            color: rgb(156 163 175);
        }

        .settings-actions {
            display: flex;
            justify-content: flex-end;
        }

        .settings-textarea {
            min-height: 110px;
            width: 100%;
            resize: vertical;
            border: 0;
            background: transparent;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5rem;
            color: rgb(17 24 39);
            outline: none;
        }

        .dark .settings-textarea {
            color: rgb(255 255 255);
        }

        @media (max-width: 900px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="settings-page">
        <x-filament::section>
            <div class="settings-tabs">
                @foreach (array_keys($this->groups) as $group)
                    <x-filament::button
                        type="button"
                        :color="$this->activeGroup === $group ? 'primary' : 'gray'"
                        :outlined="$this->activeGroup !== $group"
                        wire:click="selectGroup('{{ $group }}')"
                    >
                        {{ $group }}
                    </x-filament::button>
                @endforeach
            </div>
        </x-filament::section>

        <form wire:submit="save">
            <x-filament::section>
                <div class="settings-page">
                    @foreach ($this->getActiveGroupItems() as $item)
                        <div class="settings-row">
                            <div class="settings-row-top">
                                <h3 class="settings-label">
                                    {{ $item['display_name'] }}
                                </h3>
                            </div>

                            <div class="settings-grid">
                                <div>
                                    @if (in_array($item['type'], ['text_area', 'textarea', 'rich_text_box', 'code_editor'], true))
                                        <x-filament::input.wrapper>
                                            <textarea
                                                class="settings-textarea"
                                                wire:model.defer="values.{{ $item['id'] }}"
                                            ></textarea>
                                        </x-filament::input.wrapper>
                                    @elseif (in_array($item['type'], ['image', 'file'], true))
                                        <x-filament::input.wrapper>
                                            <x-filament::input
                                                type="file"
                                                wire:model="uploads.{{ $item['id'] }}"
                                            />
                                        </x-filament::input.wrapper>

                                        @if (! empty($values[$item['id']]))
                                            <p class="settings-current-value">
                                                Aktuell: {{ $values[$item['id']] }}
                                            </p>
                                        @endif
                                    @else
                                        <x-filament::input.wrapper>
                                            <x-filament::input
                                                type="text"
                                                wire:model.defer="values.{{ $item['id'] }}"
                                            />
                                        </x-filament::input.wrapper>
                                    @endif
                                </div>

                                <div>
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model.defer="itemGroups.{{ $item['id'] }}">
                                            @foreach ($this->getGroupOptions() as $groupOption)
                                                <option value="{{ $groupOption }}">
                                                    {{ $groupOption }}
                                                </option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="settings-actions">
                        <x-filament::button type="submit" color="primary">
                            Speichern
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </form>
    </div>
</x-filament-panels::page>