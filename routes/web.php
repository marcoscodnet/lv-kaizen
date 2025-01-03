<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\TipoUnidadController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
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
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
