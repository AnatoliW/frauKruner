<?php

/**
 * Der "Löschen"-Button für den Gutschein wird in checkout.blade.php mitten im
 * Bestellformular ausgegeben. Ein <form> darf dort nicht stehen: Der Browser
 * verwirft das innere Start-Tag, dessen </form> beendet aber das äußere
 * Formular.
 *
 * Was dabei kaputtging (in Chrome nachgestellt): Das Feld payment_id und der
 * Button "zur Zahlung" hatten danach kein Formular mehr – ein Klick darauf tat
 * schlicht nichts, der Bestellvorgang war mit angewendetem Gutschein nicht mehr
 * abzuschließen. Umgekehrt gehörte der Löschen-Button zum Bestellformular und
 * hätte statt des Gutscheins die Bestellung ausgelöst.
 *
 * Beides hing allein an dem einen verschachtelten <form>. Der Test hält deshalb
 * genau diese Zusicherung fest: Die Komponente gibt an ihrer Stelle nur den
 * Button aus, das Formular selbst wandert in den Stack 'js', der im Layout
 * außerhalb jedes Formulars gerendert wird.
 */
/**
 * Der Stack muss im selben Render-Durchlauf ausgelesen werden: Laravel leert
 * ihn, sobald die Ausgabe fertig ist. Deshalb hängt @stack('js') – wie im
 * Layout – hinter der Komponente, getrennt durch eine Marke.
 *
 * @return array{0: string, 1: string} Ausgabe an Ort und Stelle, Inhalt des Stacks
 */
function renderCouponRemoveButton(): array
{
    $output = (string) test()->blade("<x-coupon-remove-button /><!--STACK-->@stack('js')", []);

    return array_pad(explode('<!--STACK-->', $output, 2), 2, '');
}

it('gibt an Ort und Stelle kein verschachteltes Formular aus', function () {
    [$inline] = renderCouponRemoveButton();

    expect($inline)->not->toContain('<form')
        ->and($inline)->not->toContain('</form>');
});

it('verknüpft den Button über das form-Attribut mit dem ausgelagerten Formular', function () {
    [$inline, $stack] = renderCouponRemoveButton();

    expect($inline)->toContain('form="coupon-remove-form"')
        ->and($stack)->toContain('id="coupon-remove-form"')
        ->and($stack)->toContain('action="'.route('coupon.destroy').'"')
        ->and($stack)->toContain('method="post"')
        // Ohne CSRF-Feld läuft das ausgelagerte Formular in einen 419er.
        ->and($stack)->toContain('name="_token"');
});

/**
 * Das Layout gibt 'js' im Footer aus. Fiele der Stack weg oder würde er in ein
 * anderes Formular verschoben, wäre der Fehler wieder da – nur unsichtbar.
 */
it('gibt den Stack js außerhalb jedes Formulars aus', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($layout)->toContain("@stack('js')");

    $before = substr($layout, 0, strpos($layout, "@stack('js')"));

    expect(substr_count($before, '<form'))->toBe(substr_count($before, '</form>'));
});
