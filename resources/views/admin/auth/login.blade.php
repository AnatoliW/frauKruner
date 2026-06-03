<x-front_app>

    <main class="container-profile-login">
        <div class="login-section">
            <h1 class="small">Einloggen</h1>
            <form class="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                <label for="e-mail">E-Mail</label>
                <input type="email" placeholder="E-Mail-Adresse" class="@error('email') is-invalid @enderror" id="email" name="email"  value="{{ old('email') }}" required>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <label for="password">Password</label>
                <input type="password" class="form-control @error('email') is-invalid @enderror" placeholder="Passwort" id="password" name="password" minlength="8" required>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="form-group mt-3">
                    <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
                    @if ($errors->has('cf-turnstile-response'))
                        <div class="text-danger">{{ $errors->first('cf-turnstile-response') }}</div>
                    @endif
                </div>
                <input type="submit" value="Anmelden">
            </form>
            <a href="{{route('password.request')}}" class="text-grey">Passwort vergessen</a>
        </div>
        <div class="register-section">
        <h1 class="small">Ich bin neu hier und möchte:</h1>
            <div class="d-flex flex-wrap justify-content-center">
                <a href="{{ route('register') }}" class="btn btn-primary m-2">Kaufen</a>
                
                <a href="{{ route('seller.registration') }}" class="btn btn-secondary m-2">Verkaufen</a>
            </div>
            {{-- <p class="text-center">Aufgrund der hohen Nachfrage nehmen wir zur Zeit keinen neuen Verkäufer*innen mehr auf.<br>
            Du möchtest informiert werden, wenn eine Registrierung wieder möglich ist?<br> Dann schreibe eine Mail an: <a href="mailto:hilfe@fraukruner.de">hilfe@fraukruner.de</a> mit dem Betreff „Warteliste Registrierung“.<br> Wir melden uns bei dir, sobald wir wieder neue Shops zulassen.
            </p> --}}
    </div>
    </main>
</x-front_app>


