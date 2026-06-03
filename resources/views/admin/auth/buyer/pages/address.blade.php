<x-dashboard type='buyer' title="Adresse" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Nutzerdaten' => route('buyer.user.data'),
    'Verifizierung' => route('buyer.data.verify'),
    'Adresse' => route('buyer.address'),
]">

<x-user.address :data="$address" />

</x-dashboard>
