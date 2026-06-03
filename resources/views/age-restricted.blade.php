<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Nur für Volljährige – Frau Kruner</title>
    <style>
        html,
        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f6f4;
            color: #122253;
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            box-sizing: border-box;
        }

        .card {
            max-width: 32rem;
            background: #fff;
            border-radius: 0.5rem;
            padding: 2rem 1.75rem;
            box-shadow: 0 0.25rem 1.5rem rgba(18, 34, 83, 0.08);
            text-align: center;
        }

        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 1rem;
            line-height: 1.35;
        }

        p {
            margin: 0 0 1.5rem;
            line-height: 1.6;
            color: #4a4f63;
        }

        a {
            display: inline-block;
            color: #db5743;
            font-weight: 600;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <h1>Zugang nur ab 18 Jahren</h1>
            <p>Diese Website richtet sich ausschließlich an volljährige Besucherinnen und Besucher. Wenn du noch keine 18
                Jahre alt bist, kannst du Frau Kruner leider nicht nutzen.</p>
            <p><a href="{{ route('home') }}">Zurück zur Startseite</a></p>
        </div>
    </div>
</body>

</html>
