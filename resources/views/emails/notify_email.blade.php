@component('mail::message')
<h1 class="title" style="background-color: red;">Neue Verkäufer/in registriert</h1>
<div class="body-section">
<p>
Neue Verkäufer/in registriert
</p>
@php $url = route('voyager.users.edit',$user->id); @endphp
@component('mail::button', ['url' => $url, 'color' => 'green'])
Details anzeigen
@endcomponent
{{ config('app.name') }}
</div>
@endcomponent