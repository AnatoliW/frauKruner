<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zahlung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Zahlung ausführen</h1>
                        <p class="text-muted mb-4">Bitte PayPal-Transaktions-ID eingeben oder die Zahlung als kostenlos markieren.</p>

                        <form action="{{ route('admin.payment.process', $payment) }}" method="POST" class="mb-3">
                            @csrf
                            <input type="hidden" name="method" value="paypal">
                            <div class="mb-3">
                                <label for="payment_id" class="form-label">Transaktions-ID</label>
                                <input id="payment_id" name="payment_id" type="text" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Zahlung bestätigen</button>
                        </form>

                        <form action="{{ route('admin.payment.process', [$payment, 'method' => 'free']) }}" method="POST">
                            @csrf
                            <input type="hidden" name="method" value="free">
                            <button type="submit" class="btn btn-outline-secondary">Als kostenlos markieren</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>