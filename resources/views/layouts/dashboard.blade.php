@extends('layouts.app', ['type' => 'dashboard'])
@section('content')
    <div class="container-xxl profile-container">

        @includeWhen($type == 'seller', 'layouts.partial.sidebar.seller')
        @includeWhen($type == 'buyer', 'layouts.partial.sidebar.buyer')
        <main class="profile-content panel">
            <span id="profile-content-scroll-point"></span>
            <ul class="breadcrumb-userprofile">
                @foreach ($bread as $item => $url)
                    <li><a href="{{ $url }}">{{ $item }}</a></li>
                @endforeach
            </ul>

            <div class="profile-content__the-content">
                <div class="profile-content__profile-meta">
                    <h3>{{ $title }}</h3>
                    <a href="#profile-menu-scroll-point" class="btn-back"><span
                            class="arrow-back"><span></span><span></span></span></a>
                </div>
                {{ $slot }}
            </div>

        </main>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
    <script>
    function handleVideoUpload(e) {
        if (!$('#videoInput').val()) {
            // alert('Bitte füge eine Datei ein');
            e.preventDefault();  // Use e here to prevent default form behavior
            return false;
        } else {
            let fileInput = document.getElementById('videoInput');
            let file = fileInput.files[0];
            let maxSize = 1 * 1024 * 1024 * 1024; // 1GB in bytes
            let acceptableTypes = [
                "video/mp4", "video/webm", "video/ogg", "video/x-flv", "video/x-msvideo",
                "video/x-ms-wmv", "video/mp2t", "video/quicktime", "video/mpeg"
            ];

            if (file.size > maxSize) {
                alert("Die Datei ist zu groß, bitte lade ein Video, welches kleiner als 1GB ist.");
                fileInput.value = '';  // Clear the input properly
            } else if (!acceptableTypes.includes(file.type)) {
                alert("Dateityp wird nicht unterstützt, bitte lade ein Video hoch.");
                fileInput.value = '';
            } else {
                $("#videoForm").submit();
            }
        }
        e.preventDefault();  // It might be safer to always call preventDefault to manage submission manually.
    }

    function updateLoadingOverlay(percentage) {
        if (percentage < 100) {
            $('#loadingOverlay').show();
        } else {
            $('#loadingOverlay').hide();
        }
    }

    function handleFormSubmission() {
        var bar = $('.bar');
        $('#videoForm').ajaxForm({
            uploadProgress: function(event, position, total, percentComplete) {
                updateLoadingOverlay(percentComplete);
            },
            success: function(xhr) {
                toastr.success('Video wurde erfolgreich hinzugefügt');
                location.reload();
            },
            error: function(xhr) {
                console.log(xhr)
            var contentType = xhr.getResponseHeader("Content-Type");
            var errorMessage = "Es ist ein Fehler aufgetreten, bitte versuchen Sie es später noch einmal."; // Default error message

            if (xhr.status === 404) {
                errorMessage = "Der angeforderte Inhalt wurde nicht gefunden. Bitte überprüfen Sie die URL oder Ihre Netzwerkverbindung.";
            } else if (xhr.status === 500) {
                errorMessage = "Interner Serverfehler. Wir arbeiten daran, das Problem so schnell wie möglich zu beheben.";
            } else if (xhr.status === 403) {
                errorMessage = "Zugriff verweigert. Sie haben nicht die erforderlichen Berechtigungen, um auf diese Ressource zuzugreifen.";
            } else if (xhr.status === 400) {
                errorMessage = "Ungültige Anfrage. Bitte überprüfen Sie die eingegebenen Daten.";
            }else if (xhr.status === 413) {
                errorMessage = "Die Datei ist zu  groß, bitte komprimiere(verkleinere) das Video und versuche es später nochmal.";
            }

            if (contentType && contentType.indexOf("application/json") !== -1) {
                // Response is JSON
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json && json.errors && json.errors.document) {
                        $('#error').text(json.errors.document[0]);
                        toastr.error('Error: ' + json.errors.document[0]);
                    } else {
                        toastr.error(errorMessage);
                    }
                } catch (e) {
                    toastr.error(errorMessage);
                }
            } else {
                // Response is likely HTML, not JSON
                toastr.error(errorMessage);
            }
        }


        });
    }

    $(document).ready(function() {
        $('#video-upload-btn').click(handleVideoUpload);
        handleFormSubmission();
    });
</script>

@endpush
