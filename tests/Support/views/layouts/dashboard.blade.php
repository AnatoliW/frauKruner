{{-- Ersatz fuer das echte Dashboard-Layout: Dieses bindet die Seitenleiste der
     Verkaeuferin ein, die Menues, Einstellungen und Kategorien aus Tabellen
     liest, die das schlanke Testschema nicht traegt. Geprueft wird der Inhalt
     der Rechnung, nicht der Rahmen. --}}
<h1>{{ $title }}</h1>
{{ $slot }}
