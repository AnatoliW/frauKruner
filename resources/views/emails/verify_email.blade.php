@component('mail::message')
<h1 class="title">Herzlich Willkommen</h1>
<div class="body-section">
<p>
bei Frau Kruner.de <br>
Dein Konto wurde erfolgreich erstellt. Ich freue mich, dass du dabei bist. <br>
Bitte bestätige deine E-Mail-Adresse. <br><br>

@php $url = route('verify.token',['token'=>$token]); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])
Bestätigen
@endcomponent
<br><br>

Ich wünsche dir viel Spaß und tolle Erlebnisse.<br>
{{ config('app.name') }}
<br>
</p>
</div>
@endcomponent
