<x-front_app>
    <main class="container-profile-login">

        @php
            if (session()->has('user')) {
                $user = session()->get('user');
            } else {
                $user = null;
            }

        @endphp


        <div class="login-section">
            <h1 class="small mb-4">Als Verkäuferin registrieren</h1>

            <form class="login-form" action="{{ route('seller.registration.step.two') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 ">
                        <label for="vorname">Nutzername (Pseudonym)</label>
                        <input type="text" value="{{ $user ? $user->username : old('username') }}"
                            class="@error('username') is-invalid @enderror" placeholder="Nutzername (Pseudonym)"
                            id="username" name="username" required>

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="vorname">Vorname*</label>
                        <input type="text" value="{{ $user ? $user->name : old('name') }}"
                            class="@error('name') is-invalid @enderror" placeholder="Vorname" id="name"
                            name="name" required>

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="name">Nachname*</label>
                        <input type="name" value="{{ $user ? $user->last_name : old('last_name') }}"
                            class="@error('name') is-invalid @enderror" placeholder="Nachname" id="last_name"
                            name="last_name" required>
                        @error('last_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <label for="e-mail">E-Mail*</label>
                <input type="email" value="{{ $user ? $user->email : old('email') }}"
                    class="@error('email') is-invalid @enderror" placeholder="E-Mail-Adresse" id="email"
                    name="email" required>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <label for="vat">Gewerbesteuernummer</label>
                <input type="text" value="{{ $user ? $user->vat : old('meta.vat') }}" class="@error('meta.vat') is-invalid @enderror"
                    placeholder="Gewerbesteuernummer" id="vat" name="meta[vat]" required>
                @error('meta.vat')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="d-flex align-items-center">


                    <h5 class="small p-0 m-0">
                        <details class="m-0 pe-2" data-popover="up">
                            <summary class="position-relative">?</summary>
                            <div class="popoverBody">
                                Änderungen deiner Steuer müssen zu jedem 01.01., spätestens bis 15.01. angezeigt werden.
                            </div>
                        </details>
                    </h5>
                    <label for="">Kleinunternehmer*in?</label>

                    <select  id="meta.is_pay_vat"  name="meta[is_pay_vat]">
                        <option disabled>Steuerkennzeichnung</option>
                        <option value="1"  selected>Regelbesteuert</option>
                        <option value="0">Kleinunternehmen</option>
                    </select>

                </div>

                @error('is_pay_vat')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <label for="password">Password*</label>
                <input type="password" class="@error('password') is-invalid @enderror" placeholder="Passwort"
                    id="password" name="password" minlength="8" required>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <p class="text-left text-grey">Mindestens 8 oder mehr Zeichen</p>
                <input type="checkbox" id="datenschutz" name="datenschutz" required>
                <label for="datenschutz" class="visible mb-3">Hiermit bestätige ich, dass ich Ihre <a
                        href="/page/datenschutz">Datenschutzerklärung</a> und <a
                        href="/page/nutzungsbedingungen">Nutzungsbedingungen</a> zur Kenntnis genommen habe und diese
                    akzeptiere.</label>
                <input type="hidden" name="role" value="3">
                @error('role')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <input type="checkbox" id="vakuumierer" name="vakuumierer" required>
                <label for="vakuumierer" class="visible mb-3">Ich bestätige einen Vakuumierer zu besitzen und versende Ware, die Düfte enthält, ausschließlich vakuumiert.</label>
                <input type="hidden" name="role" value="3">
                @error('role')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <input type="checkbox" id="nutzungsbedingungen" name="nutzungsbedingungen" required>
                <label for="nutzungsbedingungen" class="visible mb-3">
                Ich bin mit einer Zahlung von 50€ einverstanden, wenn ich gegen den Punkt 8. der <a
                href="/page/nutzungsbedingungen">Nutzungsbedingungen</a> verstoße.</label>
                <input type="hidden" name="role" value="3">
                @error('role')
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
                <input type="submit" value="Registrieren">
            </form>

            {{-- <p class="text-center">Aufgrund der hohen Nachfrage nehmen wir zur Zeit keinen neuen Verkäufer*innen mehr
                auf.<br> --}}
            {{--  Du möchtest informiert werden, wenn eine Registrierung wieder möglich ist?<br> Dann schreibe eine Mail an: <a href="mailto:hilfe@fraukruner.de">hilfe@fraukruner.de</a> mit dem Betreff „Warteliste Registrierung“.<br> Wir melden uns bei dir, sobald wir wieder neue Shops zulassen. --}}
            {{-- </p> --}}
        </div>

    </main>
</x-front_app>
