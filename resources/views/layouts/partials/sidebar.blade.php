<nav class="navbar navbar-light navbar-vertical navbar-expand-xl">
    <script>
        var navbarStyle = localStorage.getItem("navbarStyle");
        if (navbarStyle && navbarStyle !== 'transparent') {
            document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
        }
    </script>
    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">

            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>

        </div><a class="navbar-brand" href="{{ route('home') }}">
            <div class="d-flex align-items-center py-3"><img class="me-2" src="{{ asset('images/logo_kaisen.png') }}" alt="" width="200" /><span class="font-sans-serif text-primary"></span>
            </div>
        </a>
    </div>
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">
            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">

                @can('usuario-listar')<li class="nav-item">
                    <!-- label-->
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Seguridad
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>
                    <!-- parent pages-->
                    @can('usuario-listar')<a class="nav-link" href="{{ route('users.index') }}" role="button">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-user"></span></span><span class="nav-link-text ps-1">Usuarios</span>
                        </div>
                    </a>@endcan

                    @can('rol-listar')<a class="nav-link" href="{{ route('roles.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-user-plus"></span></span><span class="nav-link-text ps-1">Roles</span>
                            </div>
                        </a>@endcan

                </li>@endcan
                <li class="nav-item">
                    <!-- label-->
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Configuración
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>


                    @can('color-listar')
                        <a class="nav-link" href="{{ route('colors.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-palette"></span></span><span class="nav-link-text ps-1">Colores</span>
                            </div>
                        </a>
                    @endcan
                    @can('concepto-listar')
                        <a class="nav-link" href="{{ route('conceptos.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-cash-register"></span></span><span class="nav-link-text ps-1">Conceptos</span>
                            </div>
                        </a>
                    @endcan
                    @can('documento-listar')
                        <a class="nav-link" href="{{ route('documentos.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-file"></span></span><span class="nav-link-text ps-1">Documentos</span>
                            </div>
                        </a>
                    @endcan
                    @can('entidad-listar')
                        <a class="nav-link" href="{{ route('entidads.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-university"></span></span><span class="nav-link-text ps-1">Entidades</span>
                            </div>
                        </a>
                    @endcan
                    @can('marca-listar')
                        <a class="nav-link" href="{{ route('marcas.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-tags"></span></span><span class="nav-link-text ps-1">Marcas</span>
                            </div>
                        </a>
                    @endcan

                    @can('modelo-listar')
                        <a class="nav-link" href="{{ route('modelos.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-cubes"></span></span><span class="nav-link-text ps-1">Modelos</span>
                            </div>
                        </a>
                    @endcan
                    @can('modelo-listar')
                        <a class="nav-link" href="{{ route('parametros.edit','1') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-file-invoice-dollar"></span></span><span class="nav-link-text ps-1">Modificar boleto</span>
                            </div>
                        </a>
                    @endcan

                    @can('sucursal-listar')
                        <a class="nav-link" href="{{ route('sucursals.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-building"></span></span><span class="nav-link-text ps-1">Sucursales</span>
                            </div>
                        </a>
                    @endcan
                    @can('ubicacion-listar')
                        <a class="nav-link" href="{{ route('ubicacions.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-box-open"></span></span><span class="nav-link-text ps-1">Ubicaciones</span>
                            </div>
                        </a>
                    @endcan
                    @can('tipo-pieza-listar')
                        <a class="nav-link" href="{{ route('tipoPiezas.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-cogs"></span></span><span class="nav-link-text ps-1">Tipos de Piezas</span>
                            </div>
                        </a>
                    @endcan
                    @can('tipo-servicio-listar')
                        <a class="nav-link" href="{{ route('tipoServicios.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-tools"></span></span><span class="nav-link-text ps-1">Tipos de Servicios</span>
                            </div>
                        </a>
                    @endcan
                    @can('tipo-unidad-listar')
                        <a class="nav-link" href="{{ route('tipoUnidads.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-layer-group"></span></span><span class="nav-link-text ps-1">Tipos de Unidades</span>
                            </div>
                        </a>
                    @endcan
                </li>
                    <li class="nav-item">
                        <!-- label-->
                        <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                            <div class="col-auto navbar-vertical-label">Administración
                            </div>
                            <div class="col ps-0">
                                <hr class="mb-0 navbar-vertical-divider" />
                            </div>
                        </div>
                        @can('cliente-listar')
                            <a class="nav-link" href="{{ route('clientes.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-user-friends"></span></span><span class="nav-link-text ps-1">Clientes</span>
                                </div>
                            </a>
                        @endcan
                        @can('pieza-listar')
                            <a class="nav-link" href="{{ route('piezas.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-cogs"></span></span><span class="nav-link-text ps-1">Piezas</span>
                                </div>
                            </a>
                        @endcan
                        @can('producto-listar')
                            <a class="nav-link" href="{{ route('productos.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-box"></span></span><span class="nav-link-text ps-1">Productos</span>
                                </div>
                            </a>
                        @endcan
                        @can('proveedor-listar')
                            <a class="nav-link" href="{{ route('proveedors.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-truck"></span></span><span class="nav-link-text ps-1">Proveedores</span>
                                </div>
                            </a>
                        @endcan
                        @can('unidad-listar')
                            <a class="nav-link" href="{{ route('unidads.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-motorcycle"></span></span><span class="nav-link-text ps-1">Unidades</span>
                                </div>
                            </a>
                        @endcan

                    </li>
                    <li class="nav-item">
                        <!-- label-->
                        <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                            <div class="col-auto navbar-vertical-label">Caja / Finanzas
                            </div>
                            <div class="col ps-0">
                                <hr class="mb-0 navbar-vertical-divider" />
                            </div>
                        </div>

                        @can('caja-listar')
                            <a class="nav-link" href="{{ route('cajas.index') }}" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-cash-register"></span></span>
                                    <span class="nav-link-text ps-1">Cajas</span>
                                </div>
                            </a>
                        @endcan

                        @can('caja-movimiento-registrar')
                            <!--<a class="nav-link" href="{{ route('movimientos.index') }}" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-exchange-alt"></span></span>
                                    <span class="nav-link-text ps-1">Movimientos</span>
                                </div>
                            </a>-->
                        @endcan

                        @php
                            $cajaAbierta = \App\Models\Caja::where('estado', 'Abierta')->first();
                        @endphp

                        @can('caja-arqueo')
                            @if($cajaAbierta)
                                <a class="nav-link" href="{{ route('cajas.arqueo.actual') }}" role="button">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon"><span class="fas fa-chart-line"></span></span>
                                        <span class="nav-link-text ps-1">Arqueo</span>
                                    </div>
                                </a>
                            @endif
                        @endcan
                    </li>
                    <li class="nav-item">
                        <!-- label-->
                        <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                            <div class="col-auto navbar-vertical-label">Stock
                            </div>
                            <div class="col ps-0">
                                <hr class="mb-0 navbar-vertical-divider" />
                            </div>
                        </div>



                        @can('movimiento-listar')
                            <a class="nav-link" href="{{ route('movimientos.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-exchange-alt"></span></span><span class="nav-link-text ps-1">Movimientos</span>
                                </div>
                            </a>
                        @endcan

                        @can('pedido-listar')
                            <a class="nav-link" href="{{ route('pedidos.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-truck"></span></span><span class="nav-link-text ps-1">Pedidos</span>
                                </div>
                            </a>
                        @endcan

                        @can('servicio-listar')
                            <a class="nav-link" href="{{ route('servicios.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-wrench"></span></span><span class="nav-link-text ps-1">Servicios</span>
                                </div>
                            </a>
                        @endcan

                        @can('stock-pieza-listar')
                            <a class="nav-link" href="{{ route('stockPiezas.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-toolbox"></span></span><span class="nav-link-text ps-1">Stock Piezas</span>
                                </div>
                            </a>
                        @endcan

                        @can('venta-pieza-listar')
                            <a class="nav-link" href="{{ route('ventaPiezas.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-shopping-cart"></span></span><span class="nav-link-text ps-1">Venta Piezas</span>
                                </div>
                            </a>
                        @endcan

                        @can('venta-listar')
                            <a class="nav-link" href="{{ route('ventas.index') }}" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-money-bill"></span></span><span class="nav-link-text ps-1">Ventas</span>
                                </div>
                            </a>
                        @endcan

                    </li>
            </ul>

        </div>
    </div>
</nav>

