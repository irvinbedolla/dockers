<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\User;
use App\Models\Documentos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

//Para sacar el Id del usuario
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ExpedienteController extends Controller
{

    public function index()
    {
        //Depediendo del rol va regresar un listado o un solo registro
        $id      = auth()->user()->id;
        $usuario = User::find($id);
        $rol     = $usuario->getRoleNames()->first();
        $persona = "Existe";
        if($rol == "Super Usuario" || $rol == 'Capacitacion Admin'){
            $personas       = Persona::all();
            $persona_buscar = Persona::where('id_usuario', $id)->get();

            if($persona_buscar == "[]"){
                $persona = "no existe";
            }
        }
        else{
            //Si la persona no existe va mandar una bandera 
            $personas = Persona::where('id_usuario', $id)->get();
            if($personas == "[]"){
                $persona = "no existe";
            }
        }
    
        return view('expedientes.index', compact('personas','persona','rol'));
    }

    public function edit($id)
    {
        $usuario = User::find($id);
        $persona = Persona::where('id_usuario', $id)->first();
        return view('expedientes.crear', compact('usuario','persona'));
    }

    public function store(Request $request)
    {
        $id = auth()->user()->id;
        $data = $request->all();
        $data_doc = [];

        
        
        $data['id_usuario'] = $id;
        
        //Validar que ya existe registro
        $persona = Persona::where(['id_usuario' => $id])->first();

        //Si no existe se va registro
        if($persona == null){
            Persona::create($data);
            return redirect()->route('expedientes')->with('success', 'Datos actualizados correctamente.'); 
        }
        //Si ya existe se va actualizar
        else{
            $capacitacion = Persona::where('id_usuario', $id)->first();
            $capacitacion->update($data);
            return redirect()->route('expedientes')->with('success', 'Datos actualizados correctamente.'); 
        }
    }

    public function personas_documentos($id){
        $id_usuario = auth()->user()->id;
        $usuario    = User::find($id_usuario);
        $rol        = $usuario->getRoleNames()->first();
        $documentos = Documentos::where('id_usuario', $id)->get();
        return view('expedientes.documentos', compact('documentos','id','rol'));
    }

    public function documento($id){
        
        $usuario    = User::find($id);
        $persona    = Persona::where('id_usuario', $id)->first();
        return view('expedientes.subir', compact('usuario','persona','id'));
    }

    public function store_documento(Request $request){
        $data = $request->all();
        $documentos = Documentos::where('id_usuario', $data["id"])->get();
        
        if(isset($data["documentoTitulo"])){
            $nombrediplomado = $data["documentoTitulo"]->getClientOriginalName();
            $path = Storage::putFileAs(
                'documentos_personal/'.$data["id"], $request->file('documentoTitulo'), $nombrediplomado
            );

            $data_doc['id_usuario'] = $data["id"];
            $data_doc['nombre']     = $data["tilulo_universitario"];
            $data_doc['documento']  = $nombrediplomado;
        }
        Documentos::create($data_doc);
        return redirect()->route('expedientes.documentos', $data["id"]);
    }

    public function destroy($id)
    {
        $documento = Documentos::find($id);
        unlink(storage_path('app/documentos_personal/'.$documento["id_usuario"].'/'.$documento->documento));
        $documento = Documentos::find($id)->delete();

        return redirect()->route('expedientes.documentos', $id);
    }
}
