@extends('layouts.app')
@section('headSection')


    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection


@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-user" aria-hidden="true"></i>Crear Usuario</h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-3">
                                <div class="form-group">
                                    <label for="name">Nombre</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" value="{{ old('name') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-6 col-md-4">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="text" class="form-control" id="email" name="email" placeholder="email" value="{{ old('email') }}">
                                </div>
                            </div>


                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="clave" value="{{ old('password') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirmar clave </label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmar clave">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-5">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control js-example-basic-single">
                                        <option value="">Seleccionar...</option>
                                        @foreach($sucursales as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id') == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-5">
                                <div class="form-group">
                                    <label for="foto">Foto</label>

                                    <input type="file" name="image" class="form-control" placeholder="">

                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-6 col-md-5">
                                <div class="form-group">
                                    <label for="roles">Roles</label>
                                    <select name="roles[]" id="roles" class="form-control" multiple>
                                        <!-- Asegúrate de generar las opciones dinámicamente -->
                                        @foreach($roles as $id => $role)
                                            <option value="{{ $id }}">
                                                {{ $role }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>


                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a href='{{ route('users.index') }}' class="btn btn-warning">Volver</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
@section('footerSection')
    <!-- jQuery 3 -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
   }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>

    <!-- page script -->
    <script>
        $(document).ready(function () {

            $('.js-example-basic-single').select2();
        });
    </script>
@endsection
