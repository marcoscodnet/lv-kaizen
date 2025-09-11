@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-tags" aria-hidden="true"></i><span class="ms-2">Ver Marca</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('marcas.update',$marca->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="@if (old('nombre')){{ old('nombre') }}@else{{ $marca->nombre }}@endif" disabled>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <strong>Tipos de unidades que comercializa la marca</strong>
                            <div class="row">
                                @foreach($tipoUnidads as $value)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <label>
                                                <input type="checkbox" name="tipos[]" value="{{ $value->id }}"
                                                       {{ $marca->tipoUnidads->contains('id', $value->id) ? 'checked' : '' }} class="name" disabled>
                                                {{ $value->nombre }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('marcas.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>


@endsection
@section('footerSection')


@endsection
