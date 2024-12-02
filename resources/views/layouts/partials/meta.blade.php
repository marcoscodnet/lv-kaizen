<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?php echo csrf_token() ?>"/>

<title>{{ config('app.name', 'Gestión Kaizen') }}</title>

<!-- Fonts -->

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

<!-- Styles -->

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

{{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}
<link href="{{ asset('css/app.css') }}" rel="stylesheet">


<div class="load">
    <div class="in"><img width="20%" src="{{ url('/images/hourglass.svg') }}"></div>
</div>

