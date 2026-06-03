@component('mail::message')
<h1 class="title" style="background-color: #E74A3F;">Bist du noch da?</h1>
<div class="body-section">
<p>
Hey,<br><br>
wir möchten sichergehen, dass du noch erreichbar bist. Mit dem Klick auf den Link bestätigst du uns, dass du noch an Verkäufen interessiert bist.<br><br>
<b>Der Link läuft in 24h ab.</b> Hast du ihn in der Zeit nicht geklickt, wird dein Profil auf FrauKruner.de automatisch auf pausieren gesetzt. Deine Artikel sind nicht mehr sichtbar.<br><br>
Du möchtest wieder verkaufen und sichtbar sein? Dann logge dich in dein Profil ein, gehe auf Profileinstellungen und drücke den Button „Profil aktivieren“.<br>
</p>
@php $url = URL::temporarySignedRoute('login', now()->addDays(2)); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])
Jetzt einloggen
@endcomponent
</div>
@endcomponent