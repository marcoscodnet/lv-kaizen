@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-credit-card" aria-hidden="true"></i><span class="ms-2">Ver Medio de Pago</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('medios.update',$medio->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="@if (old('nombre')){{ old('nombre') }}@else{{ $medio->nombre }}@endif" disabled>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="ticket" value="0">
                                    <input class="form-check-input" type="checkbox" id="ticket" name="ticket" value="1"
                                        {{ old('ticket', $medio->ticket ?? true) ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="ticket">
                                        Ticket
                                    </label>
                                </div>

                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="referencia" value="0">
                                    <input class="form-check-input" type="checkbox" id="referencia" name="referencia" value="1"
                                        {{ old('referencia', $medio->referencia ?? true) ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="referencia">
                                        Referencia
                                    </label>
                                </div>

                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="tangible" value="0">
                                    <input class="form-check-input" type="checkbox" id="tangible" name="tangible" value="1"
                                        {{ old('tangible', $medio->tangible ?? true) ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="tangible">
                                        Tangible
                                    </label>
                                </div>

                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="activa" value="0">
                                    <input class="form-check-input" type="checkbox" id="activa" name="activa" value="1"
                                        {{ old('activa', $medio->activa ?? true) ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="activa">
                                        Activa
                                    </label>
                                </div>

                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('medios.index') }}' class="btn btn-warning">Volver</a>
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
