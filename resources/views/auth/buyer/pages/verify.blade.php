<x-dashboard type='buyer' title="Verifizierung" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Nutzerdaten' => route('buyer.user.data'),
    'Verifizierung' => route('buyer.data.verify'),
]">
    <form action="{{ route('verification.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="update" value="{{ $data->id ? 1 : 0 }}">
        <label for="stadt" class="small">
            <details data-popover="up">
                <summary>?</summary>
                <div class="popoverBody">
                    Bitte gebe hier deine realen Daten an. Diese dienen der Rechnungserstellung und sind nur für dich
                    sichtbar.
                </div>
            </details>
            Adresse
        </label>
        <div class="row">
            <div class="col-12 col-md-8 mt-2">
                <input type="text" placeholder="Straße" id="straße" name="street" required
                    value="{{ $data->street ?? old('street') }}">
            </div>
            <div class="col-12 col-md-4 mt-2">
                <input type="text" placeholder="Hausnummer" id="house_num" name="house_no"
                    value="{{ $data->house_no ?? old('house_no') }}" required>
            </div>

            <div class="col-12 col-md-7 mt-2">
                <input type="text" placeholder="Ort" id="city" name="city"
                    value="{{ $data->city ?? old('city') }}" required>
            </div>

            <div class="col-12 col-md-5 mt-2">
                <input type="number" placeholder="PLZ" id="post_code" name="zip"
                    value="{{ $data->zip ?? old('zip') }}" required>
            </div>
        </div>
        

        <div class="row mt-4">

            <div class="col-12 col-md-6 mt-4">
                <label for="geburtsdatum" class="small">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte gebe hier dein Geburtsdatum ein.
                        </div>
                    </details>Geburtsdatum
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
              
            </div>
{{-- 
            <div class="col-12 col-md-6 mt-4">

                <label for="imgIDShot" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                        Zur Idenditätsprüfung und zum Fake-Check benötige ich einmalig von dir ein Foto mit deinem Personalausweis neben deinem Gesicht. Achte darauf, dass auch dein Gesicht gut zu erkennen ist. Diese Daten werden nach erfolgreicher Freischaltung gelöscht.
                        </div>
                    </details>
                    Perso-ID-Shot:
                </label>
                <input type="file" id="imgIDShot" name="id_card_front_img" accept="image/*" value="">
                @if (Storage::exists($data->id_card_front_img))
                    <a href="{{ Storage::url($data->id_card_front_img) }}">Bild ansehen</a>
                @endif
            </div> --}}

            <div class="col-12 col-md-6 mt-4">

                <label for="imgvorderseite" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte lade hier ein Foto der Vorderseite deines Personalausweises hoch. Dies dient deiner
                            Altersprüfung. Bei Fragen lies dir die <a href="/page/nutzungsbedingungen">Richtlinien durch</a>.
                        </div>
                    </details>
                    Vorderseite deines Personalausweises:
                </label>
                <input type="file" id="imgvorderseite"name="id_card_back_img" accept="image/*">
                @if ($data->id_card_back_img)
                    <a href="{{media_url($data->id_card_back_img) }}">Bild ansehen</a>
                @endif
            </div>

            <div class="col-12 col-md-6 mt-4">

                <label for="imgRueckseite" class="visible mt-3">
                    <details data-popover="up">
                        <summary>?</summary>
                        <div class="popoverBody">
                            Bitte lade hier ein Foto der Rückseite deines Personalausweises hoch. Dies dient deiner
                            Altersprüfung. Bei Fragen lies dir die  <a href="/page/nutzungsbedingungen">Richtlinien durch</a>.
                        </div>
                    </details>
                    Rückseite deines Personalausweises:
                </label>
                <input type="file" id="imgRueckseite" name="person_id_shot_img" accept="image/*">
                @if ($data->person_id_shot_img)
                    <a href="{{ media_url($data->person_id_shot_img) }}">Bild ansehen</a>
                @endif
            </div>



        </div>
        <button class="btn btn-primary mt-4" type="submit">Verifizierung senden</button>
    </form>
   

</x-dashboard>

