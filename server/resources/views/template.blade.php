<!doctype html>
<html lang="fr">

<head>
  <title>@yield('title')</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  @vite('resources/css/app.css')
</head>

<body>
  @yield('content')
  @yield('script')
</body>

</html>