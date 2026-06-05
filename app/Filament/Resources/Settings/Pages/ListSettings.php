<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ListSettings extends Page
{
    use WithFileUploads;

    protected static string $resource = SettingResource::class;

    protected string $view = 'filament.resources.settings.pages.list-settings';

    public string $activeGroup = 'Site';

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    public array $groups = [];

    /**
     * @var array<int|string, mixed>
     */
    public array $values = [];

    /**
     * @var array<int|string, TemporaryUploadedFile|UploadedFile|null>
     */
    public array $uploads = [];

    /**
     * @var array<int|string, string>
     */
    public array $itemGroups = [];

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function selectGroup(string $group): void
    {
        if (array_key_exists($group, $this->groups)) {
            $this->activeGroup = $group;
        }
    }

    public function save(): void
    {
        foreach ($this->groups as $items) {
            foreach ($items as $item) {
                $setting = Setting::query()->find($item['id']);

                if (! $setting) {
                    continue;
                }

                $upload = $this->uploads[$setting->id] ?? null;

                if ($upload instanceof TemporaryUploadedFile || $upload instanceof UploadedFile) {
                    $setting->value = $upload->store('settings', 'public');
                    $setting->save();
                    continue;
                }

                if (array_key_exists($setting->id, $this->values)) {
                    $setting->value = (string) ($this->values[$setting->id] ?? '');
                }

                if (array_key_exists($setting->id, $this->itemGroups)) {
                    $setting->group = (string) ($this->itemGroups[$setting->id] ?? $setting->group);
                }

                $setting->save();
            }
        }

        $this->loadSettings();

        Notification::make()
            ->title('Settings wurden gespeichert.')
            ->success()
            ->send();
    }

    protected function loadSettings(): void
    {
        $settings = Setting::query()
            ->orderBy('group')
            ->orderBy('order')
            ->get();

        $grouped = [];

        foreach ($settings as $setting) {
            $group = (string) ($setting->group ?: 'General');

            $grouped[$group][] = [
                'id' => (int) $setting->id,
                'display_name' => (string) ($setting->display_name ?: $setting->key),
                'key' => (string) $setting->key,
                'type' => strtolower((string) $setting->type),
                'group' => $group,
            ];

            $this->values[$setting->id] = (string) ($setting->value ?? '');
            $this->itemGroups[$setting->id] = $group;
        }

        $this->groups = $grouped;

        if (! array_key_exists($this->activeGroup, $this->groups) && $this->groups !== []) {
            $this->activeGroup = (string) array_key_first($this->groups);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('save-settings')
                    ->label('Speichern')
                    ->icon('heroicon-m-check')
                    ->color('primary')
                    ->action('save'),
                Action::make('reload-settings')
                    ->label('Neu laden')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->action(function (): void {
                        $this->loadSettings();
                    }),
            ])
                ->label('Aktionen')
                ->icon('heroicon-m-ellipsis-vertical'),
        ];
    }

    public function getActiveGroupItems(): array
    {
        return $this->groups[$this->activeGroup] ?? [];
    }

    public function getGroupOptions(): array
    {
        return array_keys($this->groups);
    }
}
