@component('mail::message')
<h1 class="title">Sorry!</h1>
<div class="body-section">
Ich freue mich, dich auf FrauKruner.de begrüßen zu dürfen. <br> <br>
Leider kam es gestern zu Serverproblemen, weshalb deine
Anmeldung nicht abgeschlossen werden konnte. Nun ist alles
startklar und du kannst hier:

@php $url = url('seller/registration/verification'); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])deine Registrierung abschließen
@endcomponent
Bei Fragen und Problemen kontaktiere mich unter hilfe@fraukruner.de oder 030 96607799. <br>
<br>
Ich freue mich auf dich.<br><br>
Herzliche Grüße<br><br>
Kathleen<br>
{{ config('app.name') }}
</div>
@endcomponent
