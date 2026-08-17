<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vertrauenswürdige Proxys
    |--------------------------------------------------------------------------
    |
    | Steht die Anwendung hinter einem Proxy (lokaler Tunnel, Load Balancer,
    | CDN), kommt die Anfrage dort per HTTPS an und wird intern per HTTP
    | weitergereicht. Ohne diesen Eintrag hält Laravel die Anfrage für
    | unverschlüsselt und erzeugt `http://`-Adressen – signierte Links
    | (URL::temporarySignedRoute) schlagen dann fehl, weil Erzeugung und
    | Prüfung von unterschiedlichen Schemata ausgehen.
    |
    | Laravels TrustProxies-Middleware liest diesen Wert selbst aus, eine
    | Registrierung in bootstrap/app.php ist nicht nötig.
    |
    | Leer lassen heißt: keinem Proxy vertrauen. Das ist die sichere Vorgabe,
    | denn wer als Proxy gilt, darf Host, Schema und Absender-IP der Anfrage
    | bestimmen. `*` gehört deshalb nur dorthin, wo die Anwendung wirklich
    | ausschließlich über den Proxy erreichbar ist – etwa lokal hinter
    | `cloudflared`.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
