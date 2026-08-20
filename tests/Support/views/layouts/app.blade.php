{{--
    Ersatz-Layout für die Tests.

    Das echte layouts.app zieht Menüs, Einstellungen und Kategorien aus Tabellen,
    die es im schlanken Test-Schema nicht gibt. Geprüft werden soll hier der
    Inhalt der eigenen Ansichten, nicht das Seitengerüst – deshalb bleibt vom
    Layout nur das @yield übrig.

    Wird über View::prependLocation() in den Tests eingehängt.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <title>@yield('title')</title>
</head>
<body>
@yield('content')
</body>
</html>
