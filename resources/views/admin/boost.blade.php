<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">{{ $type === 'Product' ? 'Produkt' : 'Profil' }} boosten</h1>
                        <form action="{{ route('admin.boost.store', [$type, $model->id]) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="package" class="form-label">Paket auswählen</label>
                                <select id="package" name="package" class="form-select" required>
                                    <option value="">Bitte wählen</option>
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}">{{ $package->title ?? $package->name ?? 'Paket ' . $package->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Weiter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>