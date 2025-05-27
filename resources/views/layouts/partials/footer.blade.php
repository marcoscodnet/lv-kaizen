<footer class="footer">
    <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
        <div class="col-12 col-sm-auto text-center">
            <p class="mb-0 text-600"><strong>Copyright &copy; 2024-{{ Carbon\carbon::now()->year }} <a href="https://codnet.com.ar" target="_blank">Cod Net</a>.</strong> Todos los derechos reservados.</p>
        </div>
        <div class="col-12 col-sm-auto text-center">
            <!--<p class="mb-0 text-600">v3.22.0</p>-->
        </div>
    </div>
    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->
    <script src="{{ asset('vendors/popper/popper.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendors/anchorjs/anchor.min.js') }}"></script>
    <script src="{{ asset('vendors/is/is.min.js') }}"></script>
    <script src="{{ asset('vendors/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ asset('vendors/lodash/lodash.min.js') }}"></script>
    <script src="{{ asset('vendors/list.js/list.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>

    <script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script>
        $(function() {
            function ajustarTabla() {
                setTimeout(function () {
                    if ($.fn.dataTable.isDataTable('#example1')) {
                        $('#example1').DataTable().columns.adjust().responsive.recalc();
                        console.log("Tabla ajustada tras toggle sidebar");
                    }
                }, 300); // un pequeño delay para que el DOM termine el cambio
            }

            $('.navbar-vertical-toggle').on('click', function() {
                ajustarTabla();
            });
        });
    </script>




@section('footerSection')
    @show
</footer>
