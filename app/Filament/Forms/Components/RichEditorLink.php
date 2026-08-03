<?php

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Ersetzt den Standard-Link-Dialog des Rich-Editors.
 *
 * Statt nur eine URL auf die aktuelle Auswahl anzuwenden, werden im Dialog
 * Linktext und URL erfasst. Der fertige Link wird als Ganzes eingefügt und der
 * Cursor landet danach ausserhalb des Links, sodass normaler Text weitergeht.
 */
class RichEditorLink
{
    public static function applyTo(RichEditor $editor): RichEditor
    {
        return $editor
            ->tools([static::tool()])
            ->registerActions([static::action()]);
    }

    /**
     * Der Toolbar-Button uebergibt zusaetzlich den aktuellen Linktext an den Dialog.
     * Steht der Cursor ohne Auswahl in einem Link, wird der komplette Link markiert,
     * damit sein Text im Dialog vorausgefuellt werden kann.
     */
    public static function tool(): RichEditorTool
    {
        $arguments = <<<'JS'
            (() => {
                const editor = $getEditor()

                if (! editor) {
                    return {}
                }

                if (editor.state.selection.empty && editor.isActive('link')) {
                    editor.chain().extendMarkRange('link').run()
                }

                const attributes = editor.getAttributes('link') ?? {}
                const selection = editor.state.selection

                return {
                    url: attributes.href ?? null,
                    shouldOpenInNewTab: attributes.target === '_blank',
                    text: selection.empty
                        ? ''
                        : editor.state.doc.textBetween(selection.from, selection.to, ' '),
                }
            })()
            JS;

        return RichEditorTool::make('link')
            ->label(__('filament-forms::components.rich_editor.tools.link'))
            ->action(arguments: $arguments)
            ->icon(Heroicon::Link)
            ->iconAlias('forms:components.rich-editor.toolbar.link');
    }

    public static function action(): Action
    {
        return Action::make('link')
            ->label(__('filament-forms::components.rich_editor.actions.link.label'))
            ->modalHeading('Link einfügen')
            ->modalWidth(Width::Large)
            ->modalSubmitActionLabel('Übernehmen')
            ->fillForm(fn (array $arguments): array => [
                'text' => $arguments['text'] ?? null,
                'url' => $arguments['url'] ?? null,
                'shouldOpenInNewTab' => $arguments['shouldOpenInNewTab'] ?? false,
            ])
            ->schema([
                TextInput::make('text')
                    ->label('Linktext')
                    ->helperText('Der Text, der im Beitrag anklickbar ist. Leer lassen, um nur den markierten Text zu verlinken.'),
                TextInput::make('url')
                    ->label('URL')
                    ->inputMode('url')
                    ->helperText('Leer lassen, um den Link zu entfernen.'),
                Checkbox::make('shouldOpenInNewTab')
                    ->label(__('filament-forms::components.rich_editor.actions.link.modal.form.should_open_in_new_tab.label')),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $editorSelection = $arguments['editorSelection'] ?? null;

                // Kein markierter Bereich: der Cursor steht nur an einer Stelle.
                $hasNoSelection = ($editorSelection['head'] ?? null) === ($editorSelection['anchor'] ?? null);

                $url = trim((string) ($data['url'] ?? ''));
                $text = trim((string) ($data['text'] ?? ''));
                $target = ($data['shouldOpenInNewTab'] ?? false) ? '_blank' : null;

                // Ohne URL wird ein bestehender Link entfernt.
                if (blank($url)) {
                    $component->runCommands(
                        [
                            ...($hasNoSelection ? [EditorCommand::make('extendMarkRange', arguments: ['link'])] : []),
                            EditorCommand::make('unsetLink'),
                        ],
                        editorSelection: $editorSelection,
                    );

                    return;
                }

                // Ohne Auswahl und ohne Linktext gibt es nichts zu verlinken – dann
                // dient die URL selbst als sichtbarer Text.
                if (blank($text) && $hasNoSelection) {
                    $text = $url;
                }

                $commands = $hasNoSelection
                    ? [EditorCommand::make('extendMarkRange', arguments: ['link'])]
                    : [];

                if (blank($text)) {
                    // Markierten Text unveraendert verlinken.
                    $commands[] = EditorCommand::make('setLink', arguments: [[
                        'href' => $url,
                        'target' => $target,
                    ]]);

                    $component->runCommands($commands, editorSelection: $editorSelection);

                    return;
                }

                // Linktext einfuegen bzw. ersetzen, danach den Link-Mark abwaehlen,
                // damit weiterer Text nicht mehr Teil des Links wird.
                $commands[] = EditorCommand::make('insertContent', arguments: [[
                    'type' => 'text',
                    'text' => $text,
                    'marks' => [[
                        'type' => 'link',
                        'attrs' => [
                            'href' => $url,
                            'target' => $target,
                        ],
                    ]],
                ]]);
                $commands[] = EditorCommand::make('unsetMark', arguments: ['link']);

                $component->runCommands($commands, editorSelection: $editorSelection);
            });
    }
}
