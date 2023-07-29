<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  @vite(['resources/css/file-browser.scss', 'resources/js/file-browser.js'])
  <title>Laravel</title>

</head>
<body class="is-preload">
<!-- Wrapper -->
<div id="wrapper">
  <div class="inner" style="margin-top: 80px">
    <section>
      <div class="init"></div>
    </section>
  </div>
</div>

@vite('resources/js/app.js')
</body>
</html>
