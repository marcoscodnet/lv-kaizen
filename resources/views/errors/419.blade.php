@extends('layouts.guest')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>

            </h1>
            <ol class="breadcrumb">

                <!--<li class="active">Data tables</li>-->
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">

                        </div>
                        @include('includes.messages')
                        <h2>419 - Sesión expirada</h2>
                        <p>Tu sesión ha expirado por inactividad o el formulario lleva mucho tiempo abierto.</p>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <!-- Panel para desloguearse -->
                        <div class="form-group">
                        <a href="{{ url()->previous() }}" class="btn btn-warning">Volver</a>

                        <a href="{{ route('login') }}" class="btn btn-primary">Ingresar</a>
                        </div>
                    </div>
                    <!-- /.box -->


                </div>
                <!-- /.col-->
            </div>
            <!-- ./row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
@endsection

