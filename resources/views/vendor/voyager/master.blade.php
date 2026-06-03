<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('css')
</head>
<body class="bg-light">
<main class="py-4">
    <div class="container">
        @yield('content')
    </div>
</main>
@stack('javascript')
</body>
</html>