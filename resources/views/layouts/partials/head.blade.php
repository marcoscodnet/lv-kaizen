<meta charset="utf-8">


  <title>Panel | Escritorio</title>
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
<link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
<meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/mstile-150x150.png') }}">
<meta name="theme-color" content="#ffffff">
<script src="{{ asset('assets/js/config.js') }}"></script>
<script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
<!-- ===============================================-->
<!--    Stylesheets-->
<!-- ===============================================-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
<link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/theme-rtl.css') }}" rel="stylesheet" id="style-rtl">
<link href="{{ asset('assets/css/theme.css') }}" rel="stylesheet" id="style-default">
<link href="{{ asset('assets/css/user-rtl.css') }}" rel="stylesheet" id="user-style-rtl">
<link href="{{ asset('assets/css/user.css') }}" rel="stylesheet" id="user-style-default">


<script>
    var isRTL = JSON.parse(localStorage.getItem('isRTL'));
    if (isRTL) {
        var linkDefault = document.getElementById('style-default');
        var userLinkDefault = document.getElementById('user-style-default');
        linkDefault.setAttribute('disabled', true);
        userLinkDefault.setAttribute('disabled', true);
        document.querySelector('html').setAttribute('dir', 'rtl');
    } else {
        var linkRTL = document.getElementById('style-rtl');
        var userLinkRTL = document.getElementById('user-style-rtl');
        linkRTL.setAttribute('disabled', true);
        userLinkRTL.setAttribute('disabled', true);
    }
</script>



   @section('headSection')
  @show
