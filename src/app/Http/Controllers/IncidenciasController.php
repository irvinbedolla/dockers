<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

//Para sacar el Id del usuario
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; //Se utiliza en la imágenes que se suben en los citados
use App\Models\Sedes;
use App\Models\Incidencias;
use Illuminate\Support\Facades\DB;

class IncidenciasController extends Controller
{   
    public function index_usuario(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        
        if($userRole[0] == "Super Usuario"){
            $incidencias = Incidencias::join('users','users.id','incidencias.id_usuario')
            ->where('estatus', "Pendiente")
            ->select('incidencias.id','incidencias.motivo','users.name','incidencias.delegacion','incidencias.created_at')
            ->get();
            return view('incidencias.index_admin',compact('incidencias'));
        }
        else{
            $incidencias = Incidencias::where('id_usuario', $id)->get();
            return view('incidencias.index_usuario',compact('incidencias'));
        }
    }

    public function crear_incidencia(){
        return view('incidencias.crear');
    }

    public function incidencias_store(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $sede = $user->delegacion;

        $data_general = [
            'motivo'                => $data["motivo"],
            'id_usuario'            => $id,
            'estatus'               => "Pendiente",
            'delegacion'            => $sede,
        ];

        Incidencias::create($data_general);  

        return redirect()->route('crear_inidencia');
    }

    public function incidencia_atender($id){
        $incidencia = Incidencias::find($id);
        return view('incidencias.atender',compact('incidencia'));
    }

    public function incidencias_update(Request $request){
        $data = $request->all();

        Incidencias::find($data["id"])->update(['estatus' => "Atendido"]);
        
        return redirect()->route('crear_inidencia');
    }
}