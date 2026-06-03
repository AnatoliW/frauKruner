@extends('layouts.app')
@section('title','Verify')
@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Bestätige deine Email-Adresse') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('Ein neuer Bestätigungslink wurde an deine E-Mail-Adresse gesendet.') }}
                        </div>
                    @endif

                    {{ __('Bevor due weitermachst, überprüfe bitte deine E-Mail auf einen Bestätigungslink.') }}
                    {{ __('Ich habe die E-Mail nicht erhalten') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('Hier klicken und einen neuen Link erhalten') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
