<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link href="{{ asset('docs/swagger-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('docs/index.css') }}" rel="stylesheet">
    <link href="{{ asset('docs/favicon-32x32.png') }}" rel="icon" sizes="32x32">
    <link href="{{ asset('docs/favicon-16x16.png') }}" rel="icon" sizes="16x16">
    @vite('resources/sass/app.scss')

    {{--    <link rel="icon" type="image/png" href="./favicon-32x32.png" sizes="32x32"/>--}}
    {{--    <link rel="icon" type="image/png" href="./favicon-16x16.png" sizes="16x16"/>--}}
</head>

<body>
<a class="btn btn-success" href="{{ route('api-docs.logout') }}"
   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
    Logout</a>
<form id="logout-form" action="{{ route('api-docs.logout') }}" method="POST" class="d-none">
    @method('DELETE')
    @csrf
</form>
<div id="swagger-ui"></div>
<script src="{{ asset('docs/swagger-ui-bundle.js') }}" defer></script>
<script src="{{ asset('docs/swagger-ui-standalone-preset.js') }}" defer></script>
<script src="{{ asset('docs/swagger-initializer.js') }}" defer></script>
</body>
</html>
