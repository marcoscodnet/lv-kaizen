<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Concepto;
use App\Models\Entidad;
use App\Models\Venta;
use DB;
use PDF;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class CajaController extends Controller
{
    use SanitizesInput;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:caja-listar', ['only' => ['index','show']]);
        $this->middleware('permission:caja-abrir', ['only' => ['abrir','store']]);

        $this->middleware('permission:caja-cerrar', ['only' => ['cerrar']]);
        $this->middleware('permission:caja-reabrir', ['only' => ['reabrir']]);
        $this->middleware('permission:caja-arqueo', ['only' => ['arqueo']]);

    }

    // Listado de cajas
    public function index()
    {
        $sucursals = Sucursal::orderBy('nombre')->pluck('nombre', 'id')->prepend('Todas', '-1');

        return view('cajas.index', compact('sucursals'));
    }

    public function dataTable(Request $request)
    {
        $columnas = [
            'cajas.apertura',
            'sucursals.nombre',
            'users.name',
            'cajas.inicial',
            'cajas.final',
            'cajas.estado'
        ];

        $columnaOrden = $columnas[$request->input('order.0.column', 0)];
        $orden = $request->input('order.0.dir', 'desc');
        $busqueda = $request->input('search.value');
        $sucursal_id = $request->input('sucursal_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = Caja::select(
            'cajas.id',
            'cajas.apertura',
            'cajas.inicial',
            'cajas.final',
            'cajas.estado',
            'sucursals.nombre as sucursal_nombre',
            'users.name as usuario_nombre'
        )
            ->leftJoin('sucursals', 'cajas.sucursal_id', '=', 'sucursals.id')
            ->leftJoin('users', 'cajas.user_id', '=', 'users.id');

        if (!empty($sucursal_id) && $sucursal_id != '-1') {
            $query->where('cajas.sucursal_id', $sucursal_id);
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('cajas.apertura', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('cajas.apertura', '<=', $fechaHasta);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($columnas, $busqueda) {
                foreach ($columnas as $columna) {
                    $q->orWhere($columna, 'like', "%$busqueda%");
                }
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = Caja::count();

        $datos = $query->orderBy($columnaOrden, $orden)
            ->skip($request->input('start', 0))
            ->take($request->input('length', 10))
            ->get()
            ->transform(function($item){
                $item->inicial = $item->inicial ?? 0;
                $item->final = $item->final ?? 0;
                return $item;
            });

        // Totales
        $totalInicial = (clone $query)->sum('cajas.inicial');
        $totalFinal = (clone $query)->sum('cajas.final');

        return response()->json([
            'data' => $datos,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => (int) $request->draw,  // <--- forzar a número
            'totales' => [
                'totalInicial' => $totalInicial,
                'totalFinal' => $totalFinal,
            ]
        ]);
    }




    public function arqueoActual()
    {
        $caja = Caja::where('estado','Abierta')->firstOrFail();
        return redirect()->route('cajas.arqueo', $caja->id);
    }


    // Abrir caja
    public function abrir()
    {
        // Obtener las sucursales disponibles para abrir caja
        $sucursals = Sucursal::where('activa', 1)->orderBy('nombre')->pluck('nombre', 'id')->prepend('', '');

        return view('cajas.abrir', compact('sucursals'));
    }

    // Guardar caja abierta
    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'inicial' => 'required|numeric|min:0',
        ]);

        $sucursalId = $this->sanitizeInput($request->input('sucursal_id'));

        // Regla de negocio: una sola caja por sucursal y por día.
        // Si ya hay una caja abierta hoy para esta sucursal, todos los usuarios
        // de la sucursal trabajan sobre esa misma caja: redirigimos a ella.
        $cajaAbierta = Caja::abiertaDelDia($sucursalId);

        if ($cajaAbierta) {
            return redirect()->route('cajas.show', ['caja' => $cajaAbierta->id])
                ->with('info', "La caja del día de esta sucursal ya está abierta (#{$cajaAbierta->id}).");
        }

        DB::beginTransaction();

        try {
            $inicial = $this->sanitizeInput($request->input('inicial'));

            $caja = Caja::create([
                'sucursal_id' => $sucursalId,
                'fecha' => now()->toDateString(),
                'user_id' => auth()->id(),
                'apertura' => now(),
                'inicial' => $inicial,
                'estado' => 'Abierta',
            ]);


            // Crear el movimiento de apertura
            $conceptoApertura = Concepto::firstOrCreate(['nombre' => 'Apertura']);
            MovimientoCaja::create([
                'caja_id' => $caja->id,
                'concepto_id' => $conceptoApertura->id,
                'entidad_id' => null,
                'venta_id' => null,
                'tipo' => 'Ingreso',
                'monto' => $request->inicial,
                'acreditado' => true,
                'fecha' => now(),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('cajas.show', ['caja' => $caja->id])
                ->with('success', 'Caja abierta correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al abrir la caja: ' . $e->getMessage());
        }
    }


    // Ver caja abierta
    public function show($id)
    {
        $caja = Caja::with([
            'movimientos.concepto','movimientos.entidad',
            'movimientos.venta.unidad',
            'movimientos.ventaPieza.piezas.pieza',
            'movimientos.servicio.cliente','movimientos.servicio.marca',
        ])->findOrFail($id);
        $conceptos = Concepto::where('activo',true)->get();
        $entidads = Entidad::where('activa',true)->get();
        return view('cajas.show', compact('caja','conceptos','entidads'));
    }



    // Cerrar caja automáticamente
    public function cerrar($id)
    {
        $caja = Caja::with('movimientos')->findOrFail($id);

        // Evitar cerrar dos veces la misma caja.
        if ($caja->estado === 'Cerrada') {
            return redirect()->route('cajas.arqueo', $caja->id)
                ->withErrors('La caja ya está cerrada.');
        }

        // Monto final = efectivo total en la caja al cerrar.
        // Incluye el fondo inicial (registrado como movimiento de "Apertura"),
        // más los ingresos acreditados del día, menos los egresos.
        $ingresosAcreditados = $caja->movimientos->where('tipo', 'Ingreso')->where('acreditado', true)->sum('monto');
        $egresos = $caja->movimientos->where('tipo', 'Egreso')->sum('monto');

        $caja->final = $ingresosAcreditados - $egresos;
        $caja->cierre = now();
        $caja->estado = 'Cerrada';
        $caja->save();

        return redirect()->route('cajas.arqueo', $caja->id)
            ->with('success', 'Caja cerrada correctamente.');
    }


    // Reabrir caja (solo administradores con permiso caja-reabrir).
    // Caso de uso: un usuario cerró la caja del día por error y, por la regla de
    // una sola caja por sucursal y por día, no puede abrir otra. El administrador
    // la reabre para poder seguir operando y volver a cerrarla al final del día.
    public function reabrir($id)
    {
        $caja = Caja::findOrFail($id);

        if ($caja->estado === 'Abierta') {
            return redirect()->route('cajas.arqueo', $caja->id)
                ->withErrors('La caja ya está abierta.');
        }

        // Al reabrir se limpian el cierre y el monto final; se recalculan cuando
        // la caja vuelva a cerrarse.
        $caja->estado = 'Abierta';
        $caja->final = null;
        $caja->cierre = null;
        $caja->save();

        return redirect()->route('cajas.show', $caja->id)
            ->with('success', 'Caja reabierta correctamente.');
    }


    // Mostrar arqueo de la caja
    public function arqueo($id)
    {
        $caja = Caja::with([
            'movimientos.concepto','movimientos.entidad',
            'movimientos.venta.unidad',
            'movimientos.ventaPieza.piezas.pieza',
            'movimientos.servicio.cliente','movimientos.servicio.marca',
        ])->findOrFail($id);

        // Totales separados por tipo y acreditado
        $totales = [
            'ingresosAcreditados' => $caja->movimientos()->where('tipo','Ingreso')->where('acreditado',true)->sum('monto'),
            'ingresosPendientes' => $caja->movimientos()->where('tipo','Ingreso')->where('acreditado',false)->sum('monto'),
            'egresos' => $caja->movimientos()->where('tipo','Egreso')->sum('monto'),
        ];

        return view('cajas.arqueo', compact('caja','totales'));
    }

    public function generateArqueoPDF($cajaId, $attach = false)
    {
        $caja = Caja::with([
            'movimientos.concepto', 'movimientos.entidad', 'user', 'sucursal',
            'movimientos.venta.unidad',
            'movimientos.ventaPieza.piezas.pieza',
            'movimientos.servicio.cliente', 'movimientos.servicio.marca',
        ])->findOrFail($cajaId);

        $data = [
            'caja' => $caja
        ];

        $pdf = PDF::loadView('cajas.arqueo_pdf', $data);

        $pdfPath = 'Arqueo_Caja_' . $cajaId . '.pdf';

        if ($attach) {
            $fullPath = public_path('/temp/' . $pdfPath);
            $pdf->save($fullPath);
            return $fullPath;
        } else {
            return $pdf->download($pdfPath);
        }
    }

    public function generateArqueoExcel($cajaId)
    {
        $caja = Caja::with([
            'movimientos.concepto', 'movimientos.entidad', 'user', 'sucursal',
            'movimientos.venta.unidad',
            'movimientos.ventaPieza.piezas.pieza',
            'movimientos.servicio.cliente', 'movimientos.servicio.marca',
        ])->findOrFail($cajaId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezado similar al PDF
        $sheet->setCellValue('A1', "Arqueo Caja #{$caja->id}");
        $sheet->setCellValue('A2', "Sucursal: {$caja->sucursal->nombre}");
        $sheet->setCellValue('A3', "Apertura: " . $caja->apertura->format('d/m/Y H:i:s'));
        $sheet->setCellValue('A4', "Usuario: {$caja->user->name}");
        $sheet->setCellValue('A5', "Estado: " . ucfirst($caja->estado));
        $sheet->setCellValue('A6', "Monto Inicial: $" . number_format($caja->inicial, 2, ',', '.'));

        if($caja->estado === 'Cerrada'){
            $sheet->setCellValue('A7', "Monto Final: $" . number_format($caja->final, 2, ',', '.'));
            $startRow = 9; // Tabla comienza debajo del encabezado
        } else {
            $startRow = 8;
        }

        // Encabezados de la tabla
        $sheet->setCellValue('A'.$startRow, 'Fecha');
        $sheet->setCellValue('B'.$startRow, 'Concepto');
        $sheet->setCellValue('C'.$startRow, 'Entidad');
        $sheet->setCellValue('D'.$startRow, 'Origen');
        $sheet->setCellValue('E'.$startRow, 'Tipo');
        $sheet->setCellValue('F'.$startRow, 'Monto');
        $sheet->setCellValue('G'.$startRow, 'Acreditado');

        $row = $startRow + 1;

        foreach($caja->movimientos as $mov){
            $sheet->setCellValue('A'.$row, $mov->fecha->format('d/m/Y H:i:s'));
            $sheet->setCellValue('B'.$row, optional($mov->concepto)->nombre ?? '-');
            $sheet->setCellValue('C'.$row, optional($mov->entidad)->nombre ?? '-');
            $sheet->setCellValue('D'.$row, $mov->origen);
            $sheet->setCellValue('E'.$row, ucfirst($mov->tipo));
            $sheet->setCellValue('F'.$row, $mov->monto);
            $sheet->setCellValue('G'.$row, $mov->tipo === 'Ingreso' ? ($mov->acreditado ? 'Sí' : 'No') : '-');
            $row++;
        }

        // Totales al final
        $totalIngresos = $caja->movimientos->where('tipo','Ingreso')->where('acreditado',true)->sum('monto');
        $totalEgresos = $caja->movimientos->where('tipo','Egreso')->sum('monto');
        $saldo = $totalIngresos - $totalEgresos;

        $sheet->setCellValue('E'.$row, 'Total Ingresos Acreditados:');
        $sheet->setCellValue('F'.$row, $totalIngresos);
        $row++;
        $sheet->setCellValue('E'.$row, 'Total Egresos:');
        $sheet->setCellValue('F'.$row, $totalEgresos);
        $row++;
        $sheet->setCellValue('E'.$row, 'Saldo Actual:');
        $sheet->setCellValue('F'.$row, $saldo);

        // Formato de moneda
        $sheet->getStyle('F'.($startRow+1).':F'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Autoajustar ancho de columnas
        foreach(range('A','G') as $col){
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'arqueo_caja_'.$caja->id.'_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }



}
