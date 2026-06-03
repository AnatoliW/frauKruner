@extends('layouts.app')

@section('content')
<div class="container-profile-login">
    <div class="row justify-content-center">
        <div class="login-section">
            <div class="mt-5">
                <div class="">
                   <h1 class="small ms-3 ">{{ __('Passwort zurücksetzen') }}</h1>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group row">
                            <!-- <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Adresse') }}</label> -->

                            <div class="">
                                <input id="email" type="email" placeholder="{{ __('E-Mail Adresse') }}" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group  mb-0">
                            <div class="">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Link zum Zurücksetzen des Passworts senden') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
