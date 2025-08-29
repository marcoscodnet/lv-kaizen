<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Documento;
class DocumentoController extends Controller
{
    use SanitizesInput;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:documento-listar|documento-crear|documento-editar|documento-eliminar', ['only' => ['index','store']]);
        $this->middleware('permission:documento-crear', ['only' => ['create','store']]);
        $this->middleware('permission:documento-editar', ['only' => ['edit','update']]);
        $this->middleware('permission:documento-eliminar', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $documentos = Documento::all();

        return view ('documentos.index',compact('documentos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('documentos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'orden' => 'nullable|integer',
            'path' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:20480',
        ], [
            'path.required' => 'El campo Archivo es obligatorio.',
            'path.mimes'    => 'El campo Archivo debe ser un documento válido (PDF, Word, Excel, etc).',
            'path.max'      => 'El Archivo no debe superar los 20 MB.',
        ]);

        $input = $this->sanitizeInput($request->except('path'));

        // Subida de archivo
        if ($request->hasFile('path')) {
            $file = $request->file('path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('files'), $filename);

            // Guardamos la ruta relativa
            $input['path'] = 'files/' . $filename;
        }

        $documento = Documento::create($input);

        return redirect()->route('documentos.index')
            ->with('success', 'Documento creado con éxito');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $documento = Documento::find($id);
        return view('documentos.show',compact('documento'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $documento = Documento::find($id);


        return view('documentos.edit',compact('documento'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Documento $documento)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'orden' => 'nullable|integer',
            'path' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:20480',
        ], [
            'path.mimes' => 'El campo Archivo debe ser un documento válido (PDF, Word, Excel, etc).',
            'path.max'   => 'El Archivo no debe superar los 20 MB.',
        ]);

        $input = $this->sanitizeInput($request->except('path'));

        // Si subió un nuevo archivo
        if ($request->hasFile('path')) {
            $file = $request->file('path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('files'), $filename);

            // Opcional: borrar archivo anterior
            if ($documento->path && file_exists(public_path($documento->path))) {
                unlink(public_path($documento->path));
            }

            $input['path'] = 'files/' . $filename;
        }

        $documento->update($input);

        return redirect()->route('documentos.index')
            ->with('success', 'Documento actualizado con éxito');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Documento::find($id)->delete();

        return redirect()->route('documentos.index')
            ->with('success','Documento eliminado con éxito');
    }
}
