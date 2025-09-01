@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-user-friends" aria-hidden="true"></i><span class="ms-2">Ver cliente</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('clientes.update',$cliente->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="@if (old('nombre')){{ old('nombre') }}@else{{ $cliente->nombre }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="documento">Documento</label>
                                    <input type="text" class="form-control" id="documento" name="documento" placeholder="Documento" value="@if (old('documento')){{ old('documento') }}@else{{ $cliente->documento }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="cuil">CUIL</label>
                                    <input type="text" class="form-control" id="cuil" name="cuil" placeholder="XX-XXXXXXXX-X" value="@if (old('cuil')){{ old('cuil') }}@else{{ $cliente->cuil }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="nacimiento">F. Nacimiento</label>
                                    <input type="date" class="form-control" id="nacimiento" name="nacimiento"  value="@if (old('nacimiento')){{ old('nacimiento') }}@else{{ ($cliente->nacimiento)?date('Y-m-d', strtotime($cliente->nacimiento)):'' }}@endif" required disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-1 col-md-2">
                                <div class="form-group">
                                    <label for="particular_area">Área</label>
                                    <input type="text" class="form-control" id="particular_area" name="particular_area" placeholder="Área" value="@if (old('particular_area')){{ old('particular_area') }}@else{{ $cliente->particular_area }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="particular">Particular</label>
                                    <input type="text" class="form-control" id="particular" name="particular" placeholder="Particular" value="@if (old('particular')){{ old('particular') }}@else{{ $cliente->particular }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-1 col-md-2">
                                <div class="form-group">
                                    <label for="celular_area">Área</label>
                                    <input type="text" class="form-control" id="celular_area" name="celular_area" placeholder="Área" value="@if (old('celular_area')){{ old('celular_area') }}@else{{ $cliente->celular_area }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" class="form-control" id="celular" name="celular" placeholder="Celular" value="@if (old('celular')){{ old('celular') }}@else{{ $cliente->celular }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-5 col-md-2">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="email" value="@if (old('email')){{ old('email') }}@else{{ $cliente->email }}@endif" disabled>
                                </div>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="calle">Calle</label>
                                    <input type="text" class="form-control" id="calle" name="calle" placeholder="Calle" value="@if (old('calle')){{ old('calle') }}@else{{ $cliente->calle }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="nro">Nro.</label>
                                    <input type="text" class="form-control" id="nro" name="nro" placeholder="Nro." value="@if (old('nro')){{ old('nro') }}@else{{ $cliente->nro }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="piso">Piso</label>
                                    <input type="text" class="form-control" id="piso" name="piso" placeholder="Piso" value="@if (old('piso')){{ old('piso') }}@else{{ $cliente->piso }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="depto">Depto.</label>
                                    <input type="text" class="form-control" id="depto" name="depto" placeholder="Depto." value="@if (old('depto')){{ old('depto') }}@else{{ $cliente->depto }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="cp">CP</label>
                                    <input type="text" class="form-control" id="cp" name="cp" placeholder="CP" value="@if (old('cp')){{ old('cp') }}@else{{ $cliente->cp }}@endif" required disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @include('includes.select-provincia-localidad', ['disabled' => true])
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="nacionalidad">Nacionalidad</label>
                                    <input type="text" class="form-control" id="nacionalidad" name="nacionalidad" placeholder="Nacionalidad" value="@if (old('nacionalidad')){{ old('nacionalidad') }}@else{{ $cliente->nacionalidad }}@endif" required disabled>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="estado_civil">E. Civil</label>

                                    <select name="estado_civil" id="estado_civil" class="form-control" required disabled>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('civiles') as $key => $label)
                                            <option value="{{ $key }}" {{ old('estado_civil', $cliente->estado_civil ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3" id="conyuge-container" style="display: none;">
                                <div class="form-group">
                                    <label for="conyuge">Cónyuge</label>
                                    <input type="text" class="form-control" id="conyuge" name="conyuge" placeholder="Cónyuge" value="@if (old('conyuge')){{ old('conyuge') }}@else{{ $cliente->conyuge }}@endif" required disabled>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="llego">Como llegó?</label>

                                    <select name="llego" id="llego" class="form-control" required disabled>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('llego') as $key => $label)
                                            <option value="{{ $key }}" {{ old('llego', $cliente->llego ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">


                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="iva">Condición IVA</label>

                                    <select name="iva" id="iva" class="form-control" required disabled>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('iva') as $key => $label)
                                            <option value="{{ $key }}" {{ old('iva', $cliente->iva ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="ocupacion">Actividad/Ocupación</label>
                                    <input type="text" class="form-control" id="ocupacion" name="ocupacion" placeholder="Actividad/Ocupación" value="@if (old('ocupacion')){{ old('ocupacion') }}@else{{ $cliente->ocupacion }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="trabajo">Lugar de trabajo</label>
                                    <input type="text" class="form-control" id="trabajo" name="trabajo" placeholder="Lugar de trabajo" value="@if (old('trabajo')){{ old('trabajo') }}@else{{ $cliente->trabajo }}@endif" disabled>
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
                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3" disabled>@if (old('observaciones')){{ old('observaciones') }}@else{{ $cliente->observaciones }}@endif</textarea>
                                    </div>
                                </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('clientes.index') }}' class="btn btn-warning">Volver</a>
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
