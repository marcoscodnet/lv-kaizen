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
use App\Http\Controllers\TipoPiezaController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\VentaPiezaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ConceptoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\MovimientoCajaController;
use App\Http\Controllers\UbicacionController;
use App\Exports\PiezasExport;

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
    Route::resource('tipoPiezas', TipoPiezaController::class);
    Route::resource('ubicacions', UbicacionController::class);

    Route::get('/clientes/exportar', [ClienteController::class, 'exportarXLS'])
        ->name('clientes.exportarXLS');

    Route::get('/clientes/exportarPDF', [ClienteController::class, 'exportarPDF'])
        ->name('clientes.exportarPDF');

    Route::resource('clientes', ClienteController::class);
    Route::post('cliente-datatable', [ClienteController::class, 'dataTable'])->name('clientes.dataTable');
    Route::post('/clientes/quickstore', [ClienteController::class, 'quickStore'])->name('clientes.quickstore');
    Route::get('/clientes/{id}/json', [ClienteController::class, 'showJson'])->name('clientes.showJson');


    Route::resource('parametros', ParametroController::class);


    Route::get('/productos/exportar', [ProductoController::class, 'exportarXLS'])
        ->name('productos.exportarXLS');

    Route::get('/productos/exportarPDF', [ProductoController::class, 'exportarPDF'])
        ->name('productos.exportarPDF');

    Route::resource('productos', ProductoController::class);
    Route::post('producto-datatable', [ProductoController::class, 'dataTable'])->name('productos.dataTable');
    Route::post('/productos/update-precio', [App\Http\Controllers\ProductoController::class, 'updatePrecio'])
        ->name('productos.updatePrecio');

    Route::resource('unidads', UnidadController::class);
    Route::post('unidad-datatable', [UnidadController::class, 'dataTable'])->name('unidads.dataTable');
    Route::get('/api/unidads-por-producto/{productoId}', [UnidadController::class, 'getUnidadsPorProducto'])->name('api.unidads.getUnidadsPorProducto');

    Route::get('/piezas/masivo', [PiezaController::class, 'createMasivo'])->name('piezas.masivo');

    Route::post('/piezas/store-masivo', [PiezaController::class, 'storeMasivo'])
        ->name('piezas.storeMasivo');

    Route::get('/piezas/exportar', [PiezaController::class, 'exportarXLS'])
        ->name('piezas.exportarXLS');

    Route::get('/piezas/exportarPDF', [PiezaController::class, 'exportarPDF'])
        ->name('piezas.exportarPDF');


    Route::resource('piezas', PiezaController::class);
    Route::post('pieza-datatable', [PiezaController::class, 'dataTable'])->name('piezas.dataTable');

    Route::get('/api/piezas/{id}', [PiezaController::class, 'getDatos'])->name('api.piezas.getDatos');
    Route::post('/piezas/ajax-store', [PiezaController::class, 'ajaxStore'])->name('piezas.ajaxStore');




    Route::resource('stockPiezas', StockPiezaController::class);
    Route::post('stockPieza-datatable', [StockPiezaController::class, 'dataTable'])->name('stockPiezas.dataTable');

    Route::get('/localidads/{provincia_id}', function ($provincia_id) {
        return \App\Models\Localidad::where('provincia_id', $provincia_id)->get();
    });

    Route::get('localidads/info/{id}', function($id){
        return \App\Models\Localidad::with('provincia')->findOrFail($id);
    });

    Route::get('/ubicaciones/{sucursal_id}', function ($sucursal_id) {
        return \App\Models\Ubicacion::where('sucursal_id', $sucursal_id)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();
    });


    Route::resource('movimientos', MovimientoController::class);
    Route::post('movimiento-datatable', [MovimientoController::class, 'dataTable'])->name('movimientos.dataTable');
    Route::get('movimiento-pdf', [MovimientoController::class, 'generatePDF'])->name('movimientos.pdf');

    Route::resource('ventaPiezas', VentaPiezaController::class);
    Route::post('ventaPieza-datatable', [VentaPiezaController::class, 'dataTable'])->name('ventaPiezas.dataTable');
    Route::get('ventaPieza-pdf', [VentaPiezaController::class, 'generatePDF'])->name('ventaPiezas.pdf');

    Route::get('/ventas/unidads', [VentaController::class, 'unidads'])->name('ventas.unidads');
    Route::resource('ventas', VentaController::class);
    Route::post('venta-datatable', [VentaController::class, 'dataTable'])->name('ventas.dataTable');
    Route::get('venta-boleto', [VentaController::class, 'generateBoleto'])->name('ventas.boleto');
    Route::get('venta-formulario', [VentaController::class, 'generateFormulario'])->name('ventas.formulario');
    Route::post('unidadsavender-datatable', [VentaController::class, 'unidadDataTable'])->name('unidadsavender.dataTable');
    Route::get('ventas/{unidad}/vender', [VentaController::class, 'vender'])->name('unidads.vender');
    Route::get('clientesearch', [ClienteController::class, 'search'])->name('cliente.search');
    Route::post('autorizar/{id}', [VentaController::class, 'autorizar'])->name('ventas.autorizar');
    Route::post('desautorizar/{id}', [VentaController::class, 'desautorizar'])->name('ventas.desautorizar');

    Route::resource('documentos', DocumentoController::class);

    Route::resource('pedidos', PedidoController::class);
    Route::post('pedido-datatable', [PedidoController::class, 'dataTable'])->name('pedidos.dataTable');

    Route::get('/servicios/unidads', [ServicioController::class, 'unidads'])->name('servicios.unidads');
    Route::get('servicios/registrar/{venta?}', [ServicioController::class, 'registrar'])->name('servicios.registrar');
    Route::resource('servicios', ServicioController::class);
    Route::post('servicio-datatable', [ServicioController::class, 'dataTable'])->name('servicios.dataTable');
    Route::get('servicio-pdf', [ServicioController::class, 'generatePDF'])->name('servicios.pdf');
    Route::post('unidadsvendidas-datatable', [ServicioController::class, 'unidadDataTable'])->name('unidadsvendidas.dataTable');

    Route::resource('conceptos', ConceptoController::class);


    // 📌 Cajas
    Route::prefix('cajas')->name('cajas.')->group(function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::post('datatable', [CajaController::class, 'dataTable'])->name('dataTable');
        Route::get('abrir', [CajaController::class, 'abrir'])->name('abrir');
        Route::post('store', [CajaController::class, 'store'])->name('store');
        Route::post('cerrar/{caja}', [CajaController::class, 'cerrar'])->name('cerrar');
        Route::get('{caja}', [CajaController::class, 'show'])->name('show');

        // 📌 Arqueo de Cajas
        Route::get('arqueo/actual', [CajaController::class, 'arqueoActual'])->name('arqueo.actual');
        Route::get('arqueo/{caja}', [CajaController::class, 'arqueo'])->name('arqueo');

        // 📌 Exportar Arqueo PDF
        Route::get('arqueo/{caja}/export-pdf', [CajaController::class, 'generateArqueoPDF'])->name('arqueo.export.pdf');

// 📌 Exportar Arqueo Excel
        Route::get('arqueo/{caja}/export-excel', [CajaController::class, 'generateArqueoExcel'])->name('arqueo.export.excel');


    });

// 📌 Movimientos de Caja
    Route::prefix('movimientos-caja')->name('movimiento_cajas.')->group(function () {
        Route::get('/', [MovimientoCajaController::class, 'index'])->name('index');
        Route::get('create/{caja}', [MovimientoCajaController::class, 'create'])->name('create');
        Route::post('store', [MovimientoCajaController::class, 'store'])->name('store');

        // EDITAR
        Route::get('edit/{movimiento}', [MovimientoCajaController::class, 'edit'])->name('edit');
        Route::put('update/{movimiento}', [MovimientoCajaController::class, 'update'])->name('update');

        // ELIMINAR
        Route::delete('destroy/{movimiento}', [MovimientoCajaController::class, 'destroy'])->name('destroy');

        // Acreditar
        Route::post('acreditar/{movimiento}', [MovimientoCajaController::class, 'acreditar'])->name('acreditar');
    });






});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
