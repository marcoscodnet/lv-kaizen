<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>

    @include('layouts.partials.head')

</head>
<body>
<!-- ===============================================-->
<!--    Main Content-->
<!-- ===============================================-->
<main class="main" id="top">
    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        @include('layouts.partials.sidebar')
        <div class="content">
        @include('layouts.partials.header')



        @yield('content')

        @show


        @include('layouts.partials.footer')
        </div>


    </div>
</main>
<!-- ===============================================-->
<!--    End of Main Content-->
<!-- ===============================================-->
<!-- @include('layouts.partials.settings')-->

</body>
</html>
