<x-filament-panels::page>
    <style>
        .settings-tabs {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }

        .settings-tab {
            min-width: 98px;
            border: 1px solid var(--gray-200);
            border-left: 0;
            background: var(--gray-50);
            color: var(--gray-600);
            padding: 13px 20px;
            border-radius: 0;
            font-size: 16px;
            font-weight: 400;
            cursor: pointer;
        }

        .settings-tab:first-child {
            border-left: 1px solid var(--gray-200);
        }

        .settings-tab.active {
            background: var(--gray-50);
            color: var(--primary-600);
            border-color: transparent;
        }

        .setting-row {
            border-bottom: 1px solid var(--gray-200);
            padding: 0 0 43px;
            margin-bottom: 34px;
        }

        .setting-row:first-of-type {
            padding-top: 0;
        }

        .setting-label {
            font-size: 24px;
            line-height: 1.25;
            color: var(--gray-950);
            font-weight: 500;
        }

        .setting-input-wrap {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 248px;
            gap: 12px;
            align-items: start;
        }

        .setting-input-wrap input[type='text'],
        .setting-input-wrap input[type='number'],
        .setting-input-wrap textarea,
        .setting-input-wrap select {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            padding: 7px 15px;
            background: var(--gray-50);
            font-size: 16px;
            line-height: 1.5;
            color: var(--gray-950);
        }

        .setting-input-wrap textarea {
            min-height: 110px;
            resize: vertical;
        }

        .group-select {
            border: 1px solid var(--gray-300);
            border-radius: 2px;
            background: var(--gray-50);
            padding: 7px 14px;
            color: var(--gray-700);
            font-size: 16px;
            font-weight: 400;
            width: 100%;
            height: 42px;
        }

        .setting-row-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .setting-action-group {
            display: inline-flex;
            gap: 14px;
            align-items: center;
            padding-right: 16px;
        }

        .setting-action-group span {
            display: block;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
        }

        .setting-action-group .up {
            border-bottom: 8px solid var(--gray-400);
        }

        .setting-action-group .down {
            border-top: 8px solid var(--gray-400);
        }

        .setting-current-value {
            margin-top: 8px;
            font-size: 13px;
            color: var(--gray-500);
        }

        .setting-caret {
            display: none;
        }

        .settings-actions {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            .setting-input-wrap {
                grid-template-columns: 1fr;
            }

            .setting-label {
                font-size: 22px;
            }
        }
    </style>

    <div>
        <div class="settings-tabs">
            @foreach (array_keys($this->groups) as $group)
                <button type="button"
                    class="settings-tab {{ $this->activeGroup === $group ? 'active' : '' }}"
                    wire:click="selectGroup('{{ $group }}')"
                >
                    {{ $group }}
                </button>
            @endforeach
        </div>

        <form wire:submit="save">
            @foreach ($this->getActiveGroupItems() as $item)
                <div class="setting-row">
                    <div class="setting-row-top">
                        <h3 class="setting-label" style="margin: 0;">{{ $item['display_name'] }}</h3>
                        <div class="setting-action-group" aria-hidden="true">
                            <span class="up"></span>
                            <span class="down"></span>
                        </div>
                        <span class="setting-caret">▲ ▼</span>
                    </div>

                    <div class="setting-input-wrap">
                        <div>
                            @if (in_array($item['type'], ['text_area', 'textarea', 'rich_text_box', 'code_editor'], true))
                                <textarea wire:model.defer="values.{{ $item['id'] }}"></textarea>
                            @elseif (in_array($item['type'], ['image', 'file'], true))
                                <x-filament::input.wrapper wire:target="uploads.{{ $item['id'] }}">
                                    <x-filament::input
                                        type="file"
                                        wire:model="uploads.{{ $item['id'] }}"
                                    />
                                </x-filament::input.wrapper>

                                @if (! empty($values[$item['id']]))
                                    <p class="setting-current-value">Current: {{ $values[$item['id']] }}</p>
                                @endif
                            @else
                                <input type="text" wire:model.defer="values.{{ $item['id'] }}">
                            @endif
                        </div>

                        <div>
                            <select class="group-select" wire:model.defer="itemGroups.{{ $item['id'] }}">
                                @foreach ($this->getGroupOptions() as $groupOption)
                                    <option value="{{ $groupOption }}">{{ $groupOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="settings-actions">
                <x-filament::button type="submit" color="primary">
                    Speichern
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
