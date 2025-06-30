<?php

namespace App\Http\Controllers;

use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use App\Models\Parametro;

class ParametroController extends Controller
{
    use SanitizesInput;
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $parametro = Parametro::find($id);


        return view('parametros.edit',compact('parametro'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'contenido' => 'required',


        ]);

        $input = $this->sanitizeInput($request->all());




        $parametro = Parametro::find($id);
        $parametro->update($input);



        return redirect()->route('clientes.index')
            ->with('success','Boleto modificado con éxito');
    }
}
