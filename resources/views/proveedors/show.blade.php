@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-truck" aria-hidden="true"></i><span class="ms-2">Ver proveedor</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('proveedors.update',$proveedor->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="@if (old('nombre')){{ old('nombre') }}@else{{ $proveedor->nombre }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="razon">Razón Social</label>
                                    <input type="text" class="form-control" id="razon" name="razon" placeholder="Razon" value="@if (old('razon')){{ old('razon') }}@else{{ $proveedor->razon }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="cuil">CUIL</label>
                                    <input type="text" class="form-control" id="cuil" name="cuil" placeholder="XX-XXXXXXXX-X" value="@if (old('cuil')){{ old('cuil') }}@else{{ $proveedor->cuil }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="iva">Condición IVA</label>

                                    <select name="iva" id="iva" class="form-control" required disabled>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('iva') as $key => $label)
                                            <option value="{{ $key }}" {{ old('iva', $proveedor->iva ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-1">
                                <div class="form-group">
                                    <label for="particular_area">Área</label>
                                    <input type="text" class="form-control" id="particular_area" name="particular_area" placeholder="Área" value="@if (old('particular_area')){{ old('particular_area') }}@else{{ $proveedor->particular_area }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="particular">Particular</label>
                                    <input type="text" class="form-control" id="particular" name="particular" placeholder="Particular" value="@if (old('particular')){{ old('particular') }}@else{{ $proveedor->particular }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-1">
                                <div class="form-group">
                                    <label for="celular_area">Área</label>
                                    <input type="text" class="form-control" id="celular_area" name="celular_area" placeholder="Área" value="@if (old('celular_area')){{ old('celular_area') }}@else{{ $proveedor->celular_area }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" class="form-control" id="celular" name="celular" placeholder="Celular" value="@if (old('celular')){{ old('celular') }}@else{{ $proveedor->celular }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="email" value="@if (old('email')){{ old('email') }}@else{{ $proveedor->email }}@endif" disabled>
                                </div>
                            </div>


                        </div>


                        <div class="row">

                                <div class="form-group">
                                    <div class="row">
                                        <label for="observaciones" class="col-md-12">Observaciones</label>
                                    </div>

                                    <!-- Fila 2: Área de texto -->
                                    <div class="row">
                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3" disabled>@if (old('observaciones')){{ old('observaciones') }}@else{{ $proveedor->observaciones }}@endif</textarea>
                                    </div>
                                </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('proveedors.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- /.content-wrapper -->
@endsection
@section('footerSection')



@endsection
