<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\TipoUnidadController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\PiezaController;
use App\Http\Controllers\StockPiezaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\EntidadController;
use App\Http\Controllers\TipoServicioController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(route('login'));
});

Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::get('perfil', [UserController::class, 'perfil'])->name('users.perfil');
    Route::post('updatePerfil', [UserController::class, 'updatePerfil'])->name('users.updatePerfil');
    Route::post('user-datatable', [UserController::class, 'dataTable'])->name('users.dataTable');

    Route::resource('sucursals', SucursalController::class);
    Route::resource('tipoUnidads', TipoUnidadController::class);
    Route::resource('marcas', MarcaController::class);
    Route::resource('modelos', ModeloController::class);
    Route::post('modelo-datatable', [ModeloController::class, 'dataTable'])->name('modelos.dataTable');
    Route::resource('colors', ColorController::class);
    Route::resource('entidads', EntidadController::class);
    Route::resource('tipoServicios', TipoServicioController::class);

    Route::resource('clientes', ClienteController::class);
    Route::post('cliente-datatable', [ClienteController::class, 'dataTable'])->name('clientes.dataTable');

    Route::resource('parametros', ParametroController::class);

    Route::resource('productos', ProductoController::class);
    Route::post('producto-datatable', [ProductoController::class, 'dataTable'])->name('productos.dataTable');

    Route::resource('unidads', UnidadController::class);
    Route::post('unidad-datatable', [UnidadController::class, 'dataTable'])->name('unidads.dataTable');

    Route::resource('piezas', PiezaController::class);
    Route::post('pieza-datatable', [PiezaController::class, 'dataTable'])->name('piezas.dataTable');

    Route::get('/api/piezas/{id}', [PiezaController::class, 'getDatos'])->name('api.piezas.getDatos');



    Route::resource('stockPiezas', StockPiezaController::class);
    Route::post('stockPieza-datatable', [StockPiezaController::class, 'dataTable'])->name('stockPiezas.dataTable');

    Route::get('/localidads/{provincia_id}', function ($provincia_id) {
        return \App\Models\Localidad::where('provincia_id', $provincia_id)->get();
    });

});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
