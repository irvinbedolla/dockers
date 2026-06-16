<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//agregamos
use App\Models\Registro;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;


class RegistroController extends Controller
{
    //Se agrega un constructor
    function __contruct(){
        $this->middleware('permission:ver-rol | crear-rol | editar-rol | borrar-rol', ['only'=>['index']]);
        $this->middleware('permission:crear-rol', ['only'=>['create','store']]);
        $this->middleware('permission:editar-rol',['only'=>['edit','update']]);
        $this->middleware('permission:borrar-rol',['only'=>['destroy']]);


    }

    public function index(){
        $registros = Registro::paginate(10);
        return view('registro.index', compact('registros'));
    }

    public function create(){
        return view('registro.crear');
    }

    public function store(Request $request){
        $data = $request->all();

        //Validar documentacion
        request()->validate([
            'nombre'    => 'required',
            'correo'    => 'required',
            'celular'   => 'required',
            'estado'    => 'required',
            'genero'    => 'required',
        ], $data);

        $data["estatus"] = "Validado";

        Registro::create($data);  
        return redirect()->route('registro'); 
    }

    public function edit($id){
        $registro = Registro::find($id);
        return view('registro.editar', compact('registro'));
            
    }


    public function update(Request $request,$id){
        $registro = Registro::find($id);
        $data_update= array(
            'estatus' => $request["genero"],
        );
        $registro->update($data_update);
        
        return redirect()->route('registro');
    }
}
