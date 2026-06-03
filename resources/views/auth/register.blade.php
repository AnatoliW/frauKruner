<x-front_app>
    <main class="container-profile-login">
        <div class="login-section">
            <h1 class="small">Registrierung</h1>
            <form class="login-form" action="{{ route('register') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6">
                        <label for="vorname">Vorname</label>
                        <input type="vorname" class="@error('username') is-invalid @enderror" placeholder="Vorname"
                            id="name" name="name" required>

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="name">Name</label>
                        <input type="name" class="@error('name') is-invalid @enderror" placeholder="Name"
                            id="last_name" name="last_name" required>
                        @error('last_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <label for="e-mail">E-Mail</label>
                <input type="email" class="@error('email') is-invalid @enderror" placeholder="E-Mail-Adresse"
                    id="email" name="email" required>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <label for="password">Password</label>
                <input type="password" class="@error('password') is-invalid @enderror" placeholder="Passwort"
                    id="password" name="password" minlength="8" required>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="form-group mt-2">
                    <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
                    @error('cf-turnstile-response')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <p class="text-left text-grey">Mindestens 8 oder mehr Zeichen</p>
                <input type="checkbox" id="datenschutz" name="datenschutz" required>
              
                <label for="datenschutz" class="visible">Hiermit bestätige ich, dass ich Ihre <a
                        href="/page/datenschutz">Datenschutzerklärung</a> und <a
                        href="/page/agb-widerrufsbelehrung-muster-widerruf">Nutzungsbedingungen</a> zur Kenntnis
                    genommen habe und diese akzeptiere.</label>
                <input type="hidden" name="role" value="2">
                @error('role')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <input type="submit" value="Registrieren">
            </form>
        </div>

    </main>
</x-front_app>
