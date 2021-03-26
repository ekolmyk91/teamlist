<!DOCTYPE html>
<html>
<head lang="{{ app()->getLocale() }}">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Web4Pro Team</title>
    <link rel="shortcut icon" href="favicon.ico">
    <!-- CSS -->
    {{--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--}}
    <link href="{{ asset('css/application.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,800" rel="stylesheet">
    <link rel="stylesheet"
          href="https://mk0herothemesdek9380.kinstacdn.com/wp-content/plugins/heroic-social-widget/css/font-awesome.min.css"
          type="text/css" media="all">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
</head>
<body>
<div id="app"></div>
<div class="overlay"></div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
