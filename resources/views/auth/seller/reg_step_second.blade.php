<x-front_app>
    <main class="container-profile-login">
        <div class="login-section">
            <h1 class="small">Als Verkäuferin registrieren</h1>
            <form class="login-form" action="{{ route('verification.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="stadt" class="visible">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte gebe hier deine realen Daten an. Diese dienen der Rechnungserstellung und sind nur für dich sichtbar
                        </div>
                    </details>
                    Adresse
                </label>
                <div class="row">
                    <div class="col-8">
                        <input type="text" placeholder="Straße" id="street" name="street" required value="{{old('street')}}">
                    </div>
                    <div class="col-4">
                        <input type="text" placeholder="Hausnummer" id="house_num" name="house_no" value="{{old('house_no')}}" required>
                    </div>

                    <div class="col-7">
                        <input type="text" placeholder="Ort" id="city" name="city" value="{{old('city')}}" required>
                    </div>

                    <div class="col-5">
                        <input type="number" placeholder="PLZ" id="post_code" name="zip" value="{{old('zip')}}" required>
                    </div>
                </div>


                <label for="iban" class="visible mt-5">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte gebe hier deine IBAN an. Du erhältst hier deine Verkaufsauszahlungen.
                        </div>
                    </details>
               IBAN
                </label>
                <input type="text" placeholder="IBAN" id="iban" name="iban" value="{{old('iban')}}" required>
                <label for="bic" class="visible ">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte gebe hier die BIC deiner Bank an.
                        </div>
                    </details>
               BIC
                </label>
                <input type="text" placeholder="BIC" id="bic" name="bic" value="{{old('bic')}}" required>



                <label for="geburtsdatum" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte gebe hier dein Geburtsdatum ein.
                        </div>
                    </details>
                    Geburtsdatum
                </label>
                @php
                $today = Carbon\Carbon::now();
                $today->subYears(18);
                @endphp
      
                <?php
                $year = (int)date("$today");
             
                // $year = -18;
             
                $year = (string)$year;

                $year = $year . '-' . date("m") . '-' . date("d");
                echo "<input type='date' placeholder='Geburtsdatum'  id='geburtsdatum' name='date_of_birth'  max='$year' required>";
                ?>
                <!-- <input type="date" placeholder="Geburtsdatum" id="geburtsdatum" name="date_of_birth" value="{{old('date_of_birth')}}" required> -->


                <label for="imgvorderseite" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte lade hier ein Foto der Vorderseite deines Personalausweises hoch. Dies dient deiner Altersprüfung. Bei Fragen lies dir die <a href="/page/nutzungsbedingungen">Richtlinien durch</a>.
                        </div>
                    </details>
                    Vorderseite deines Personalausweises:
                </label>
                <input type="file" id="imgvorderseite" name="id_card_front_img" accept="image/*" required>

                <label for="imgRueckseite" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte lade hier ein Foto der Rückseite deines Personalausweises hoch. Dies dient deiner Altersprüfung. Bei Fragen lies dir die  <a href="/page/nutzungsbedingungen">Richtlinien durch</a>.
                        </div>
                    </details>
                    Rückseite deines Personalausweises:
                </label>
                <input type="file" id="imgRueckseite" name="id_card_back_img" accept="image/*" required>

                <label for="imgIDShot" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Zur Idenditätsprüfung und zum Fake-Check benötige ich einmalig von dir ein Foto mit deinem Personalausweis neben deinem Gesicht. Achte darauf, dass auch dein Gesicht gut zu erkennen ist. Diese Daten werden nach erfolgreicher Freischaltung gelöscht.
                        </div>
                    </details>
                    Perso-ID-Shot:
                </label>
                <input type="file" id="imgIDShot" name="person_id_shot_img" accept="image/*" required>


                <input type="checkbox" id="datenschutz" name="datenschutz" class="mt-3" required>
                <label for="datenschutz" class="visible">
                    Hiermit bestätige ich, dass ich Ihre <a href="/page/datenschutz">Datenschutzerklärung</a> und  <a href="/page/nutzungsbedingungen">Nutzungsbedingungen</a> zur Kenntnis genommen habe und diese akzeptiere.</label>
                 <div>
                    
                <input type="submit" value="Registrieren"><!-- Go to the next step (Upload Images for age and person verification)-->
               <!-- <a class="btn btn-secondary" href="{{route('user.delete')}}">Back</a>-->
                 </div>
            </form>


        </div>

    </main>

</x-front_app>