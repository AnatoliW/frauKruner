@extends('layouts.app')

@section('content')
<div class="container-profile-login mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="mt-5">
            <div class="">
                <div class="">
                    <h3 class="small ms-3">{{ __('Passwort zurücksetzen') }}</h3>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <!-- <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Adresse') }}</label> -->

                            <div class="">
                                <input id="email" type="email" placeholder="{{ __('E-Mail Adresse') }}" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <!-- <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label> -->

                            <div class="mt-3">
                                <input id="password" type="password" placeholder="{{ __('Password') }}" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <!-- <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Passwort bestätigen') }}</label> -->

                            <div class="mt-3">
                                <input id="password-confirm" type="password" placeholder="{{ __('Passwort bestätigen') }}" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Passwort zurücksetzen') }}
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
