<?php

namespace App\Filament\Forms\Components;

use BackedEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Bildgroessen-Presets fuer den Rich-Editor.
 *
 * Wird ein Bild im Beitragstext angeklickt, blendet der Editor darunter eine
 * schwebende Leiste mit festen Breiten ein. Die Werte orientieren sich an der
 * Textspalte der Beitragsseite (.news-single-content, max. 600px), damit die
 * im Editor gewaehlte Breite auf der Website exakt so ankommt.
 *
 * Das freie Ziehen an den Bildecken (resizableImages) bleibt daneben bestehen.
 */
class RichEditorImageSize
{
    /**
     * Breite der Textspalte auf der Beitragsseite – Bezugsgroesse fuer die
     * Presets und fuer die Proportionen der Button-Icons.
     */
    protected const CONTENT_WIDTH = 600;

    /**
     * @var array<string, array{label: string, width: int|null}>
     */
    protected const PRESETS = [
        'imageWidthSmall' => ['label' => 'Klein – 200 px', 'width' => 200],
        'imageWidthMedium' => ['label' => 'Mittel – 300 px', 'width' => 300],
        'imageWidthLarge' => ['label' => 'Groß – 450 px', 'width' => 450],
        'imageWidthFull' => ['label' => 'Volle Breite – 600 px', 'width' => 600],
        'imageWidthOriginal' => ['label' => 'Originalgröße wiederherstellen', 'width' => null],
    ];

    public static function applyTo(RichEditor $editor): RichEditor
    {
        return $editor
            ->tools(static::tools())
            ->floatingToolbars([
                // Die Standard-Leisten (aktuell nur die Tabellen-Leiste) bleiben erhalten.
                ...$editor->getDefaultFloatingToolbars(),
                'image' => array_keys(static::PRESETS),
            ]);
    }

    /**
     * @return array<RichEditorTool>
     */
    public static function tools(): array
    {
        $tools = [];

        foreach (static::PRESETS as $name => $preset) {
            $tools[] = RichEditorTool::make($name)
                ->label($preset['label'])
                ->icon(static::icon($preset['width']))
                ->jsHandler(static::jsHandler($preset['width']))
                ->activeJsExpression(static::activeJsExpression($preset['width']));
        }

        return $tools;
    }

    /**
     * Setzt die Breite am ausgewaehlten Bild und entfernt eine zuvor per
     * Ziehen gesetzte feste Hoehe, damit das Seitenverhaeltnis erhalten bleibt.
     */
    protected static function jsHandler(?int $width): string
    {
        $js = <<<'JS'
            (() => {
                const editor = $getEditor()

                if (! editor) {
                    return
                }

                const width = __WIDTH__
                const selection = editor.state.selection

                editor.chain().focus().updateAttributes('image', { width, height: null }).run()

                // Die Node-View des Editors uebernimmt geaenderte Attribute nicht von
                // selbst – die neue Breite wird deshalb direkt am Bild nachgezogen.
                if (! selection.node || selection.node.type.name !== 'image') {
                    return
                }

                const nodeElement = editor.view.nodeDOM(selection.from)

                if (! (nodeElement instanceof HTMLElement)) {
                    return
                }

                const image = nodeElement.tagName === 'IMG'
                    ? nodeElement
                    : nodeElement.querySelector('img')

                if (! image) {
                    return
                }

                image.style.width = width ? `${width}px` : ''
                image.style.height = ''
            })()
            JS;

        return str_replace('__WIDTH__', $width === null ? 'null' : (string) $width, $js);
    }

    /**
     * Hebt den Button hervor, der die aktuell gesetzte Breite abbildet. Der
     * Wert kann als Zahl oder als String aus dem gespeicherten HTML kommen,
     * deshalb der lose Vergleich.
     */
    protected static function activeJsExpression(?int $width): string
    {
        if ($width === null) {
            return '! $getEditor()?.getAttributes(\'image\')?.width';
        }

        return '$getEditor()?.getAttributes(\'image\')?.width == '.$width;
    }

    /**
     * Rechteck-Icon, dessen Fuellung die Breite im Verhaeltnis zur Textspalte zeigt.
     */
    protected static function icon(?int $width): string|BackedEnum|Htmlable
    {
        if ($width === null) {
            return Heroicon::ArrowUturnLeft;
        }

        $fill = round(20 * min($width / static::CONTENT_WIDTH, 1), 2);

        return new HtmlString(<<<SVG
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.5" opacity="0.35"/>
                <rect x="2" y="5" width="{$fill}" height="14" rx="2" fill="currentColor"/>
            </svg>
            SVG);
    }
}
