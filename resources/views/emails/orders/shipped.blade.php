@component('mail::message')

<h1 class="title">Feedback</h1>
<div class="body-section">
<p>Liebe/r {{ $order->user->username ?? $order->user->name }},</p>
<p>wir legen sehr viel Wert auf Kundenzufriedenheit.<br>
Mit deinem Feedback hilfst du uns, besser zu werden.</p>

@php $url = 'https://bit.ly/feedbackfxxk'; @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])Feedback geben
@endcomponent
<p>
Ich freue mich auf deine Bewertung!<br><br>
Herzliche Grüße<br><br>
Kathleen<br></p>
{{ config('app.name') }}
</div>
@endcomponent