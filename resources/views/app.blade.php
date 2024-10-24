<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" value="{{ csrf_token() }}" />
    <title>{{ env('APP_NAME', 'WMS') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
</head>

<body class="font-sans antialiased">
    @if (Auth::check())
    <script>
        window.WMS = {!!json_encode([
            'isLoggedIn' => true,
            'user' => Auth::user()
        ])!!}
    </script>
    @else
    <script>
        window.WMS = {!!json_encode([
            'isLoggedIn' => false
        ])!!}
    </script>
    @endif

    <div id="app"></div>
    <script src="{{ mix('js/app.js') }}" type="text/javascript"></script>
</body>

</html>
