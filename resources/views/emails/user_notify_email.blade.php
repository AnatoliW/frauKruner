@component('mail::message')
<h1 class="title">{{ $data->title }}</h1>
<div class="body-section">
<p>
{!! $data->body !!}
</p>
@if ($data->button_link)
@component('mail::button', ['url' => $data->button_link])
{{$data->button_text}}
@endcomponent
@endif
{{ config('app.name') }}
</div>
@endcomponent
