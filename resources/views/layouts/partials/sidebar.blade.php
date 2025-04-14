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
                    @can('sucursal-listar')
                        <a class="nav-link" href="{{ route('sucursals.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-building"></span></span><span class="nav-link-text ps-1">Sucursales</span>
                            </div>
                        </a>
                    @endcan
                    @can('tipo-unidad-listar')
                        <a class="nav-link" href="{{ route('tipoUnidads.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-layer-group"></span></span><span class="nav-link-text ps-1">Tipos de Unidades</span>
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
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-cogs"></span></span><span class="nav-link-text ps-1">Modelos</span>
                            </div>
                        </a>
                    @endcan
                    @can('color-listar')
                        <a class="nav-link" href="{{ route('colors.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-palette"></span></span><span class="nav-link-text ps-1">Colores</span>
                            </div>
                        </a>
                    @endcan
                    @can('entidad-listar')
                        <a class="nav-link" href="{{ route('entidads.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-university"></span></span><span class="nav-link-text ps-1">Entidades</span>
                            </div>
                        </a>
                    @endcan
                    @can('tipo-servicio-listar')
                        <a class="nav-link" href="{{ route('tipoServicios.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-tools"></span></span><span class="nav-link-text ps-1">Tipos de Servicios</span>
                            </div>
                        </a>
                    @endcan
            </ul>

        </div>
    </div>
</nav>

