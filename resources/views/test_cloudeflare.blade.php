

<!DOCTYPE html>
<html>
<head>
    <title>Form with CAPTCHA</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Form with CAPTCHA</h2>
        <form method="POST" action="{{route('test.cloudeflare')}}">
            @csrf
            <!-- Your form fields -->
            
            <!-- Turnstile CAPTCHA widget -->
            <div class="form-group">
                <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
                @if ($errors->has('cf-turnstile-response'))
                    <div class="text-danger">{{ $errors->first('cf-turnstile-response') }}</div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        
        @if ($errors->has('captcha'))
            <div class="alert alert-danger mt-3">
                {{ $errors->first('captcha') }}
            </div>
        @endif
    </div>
</body>
</html>
