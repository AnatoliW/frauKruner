<x-front_app>
@section('title', 'fraukruner.de')
@section('description', 'Eintauchen in die Welt der Lust.')

<main class="container-profile-login">
    <div class="login-section">
        <h1 class="small">Einloggen</h1>
        <form class="login-form" action="https://fraukruner.de/login">
            <label for="e-mail">E-Mail</label>
            <input type="email" placeholder="E-Mail-Adresse" id="e-mail" name="e-mail" required>
            <label for="password">E-Mail</label>
            <input type="password" placeholder="Passwort" id="password" name="password" minlength="8" required>
            <input type="submit" value="Anmelden">
        </form>
        <a href="/login-templ.html" class="text-grey">Passwort vergessen</a>
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