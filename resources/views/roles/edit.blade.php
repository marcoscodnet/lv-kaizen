@extends('layouts.app')
@section('headSection')

    <!-- AdminLTE Skins. Choose a skin from the css/skins
           folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-user-plus" aria-hidden="true"></i><span class="ms-2">Editar rol</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('roles.update',$role->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    @include('includes.messages')
                    <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-b5247297-25bf-45ab-80de-5d87d5130cfa" id="dom-b5247297-25bf-45ab-80de-5d87d5130cfa">
                        <div class="mb-3">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" value="@if (old('name')){{ old('name') }}@else{{ $role->name }}@endif">
                        </div>
                        <div class="mb-3">
                            <strong>Permisos:</strong>
                            <div class="row">
                                @foreach($permissions as $group => $perms)
                                    <h6 class="mt-3"><strong>{{ ucfirst($group) }}</strong></h6>
                                    <div class="row">
                                        @foreach($perms as $value)
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <label>
                                                        <input class="name" type="checkbox" name="permission[]" value="{{ $value->id }}"
                                                               @foreach ($role->permissions as $role_permit)
                                                                   @if ($role_permit->id == $value->id) checked @endif
                                                            @endforeach
                                                        >
                                                        {{ ucfirst(str_replace('-', ' ', $value->name)) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach

                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('roles.index') }}' class="btn btn-warning">Volver</a>
                            </div>
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
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('dist/js/demo.js') }}"></script>

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->

@endsection
