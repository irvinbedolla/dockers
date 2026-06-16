<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SeerChatP; 
use App\Models\SeerChatR; 
use App\Models\SeerChatRP;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, HasRoles;

    public function solicitud()
    {
        return view('solicitud');
    }

    //Funciones para chat
    public function chats()
    {   
        $preguntasChats = SeerChatP::all();
        return view('chat', compact('preguntasChats'));
    }
      
    public function store_chat(Request $request)
    {
        $data = $request->all();
        
        //validando información
        $validatedData=$request->validate([
            'nombre_completo' => 'required|string',
            'ciudad'          => 'required|string',
            'idPregunta'      => 'required|numeric',
        ]);
        //Se crea un arreglo para insertar la información
        $data_insert=array(
            'nombre_completo'  => $data["nombre_completo"],
             'ciudad'=> $data["ciudad"],
        );
        //dd($data_insert);
        SeerChatR::create($data_insert); 

        $idRegistro = SeerChatR::latest('id')->first();
        $data_insertP=array(
            'id_registro'  => $idRegistro->id,
            'id_pregunta'  => $data["idPregunta"],
        );
        //dd($data_insertP);
        SeerChatRP::create($data_insertP); 
        //Obtienes Ciudad y el nombre
        $registro=SeerChatR::find($idRegistro->id);
        //Obtiene todas las preguntas
        $preguntasChats=SeerChatP::all();
        //ID de registro
        $id=$idRegistro->id;
        $res=SeerChatRP::join('chat_preguntas','chat_preguntas.id','=','chat_rp.id_pregunta')
        ->join('chat_registro','chat_registro.id','=','chat_rp.id_registro')
        ->where('chat_registro.id',$id)
        ->select('chat_preguntas.pregunta','chat_preguntas.respuesta','chat_rp.id')
        ->orderBy('chat_rp.id', 'DESC')
        ->get();
        $idPregunta = SeerChatRP::latest('id_pregunta')->first();
        $ver_res = null;

        return view('RespuestasChat', compact('id','registro','res','ver_res','preguntasChats','idPregunta'));
    }

    public function storeUno(Request $request)
    {
        $data = $request->all();
        
        //validando información
        $validatedData=$request->validate([
            'idPregunta'      => 'required|numeric',
            'id'              => 'required|numeric'
        ]);
        $data_insertP=array(
            'id_registro'  =>  $data["id"],
            'id_pregunta'  => $data["idPregunta"],
        );
        //dd($data_insertP);
        SeerChatRP::create($data_insertP); 

        //Obtienes Ciudad y el nombre
        $registro=SeerChatR::find($data["id"]);
        //Obtiene todas las preguntas
        $preguntasChats=SeerChatP::all();
        //Historial de preguntas
        $res=SeerChatRP::join('chat_preguntas','chat_preguntas.id','=','chat_rp.id_pregunta')
        ->join('chat_registro','chat_registro.id','=','chat_rp.id_registro')
        ->where('chat_registro.id',$data["id"])
        ->select('chat_preguntas.pregunta','chat_preguntas.respuesta','chat_rp.id')
        ->orderBy('chat_rp.id', 'ASC')
        ->get();
        //dd($res);
        //ID de registro
        $id=$data["id"];
        //$datos = SeerChatR::latest()->first();
        $idPregunta = SeerChatRP::latest('id_pregunta')->first();
        //Obtiene la ultima pregunta
        $ver_res=SeerChatP::find($idPregunta->id_pregunta);

        return view('RespuestasChat', compact('id','registro','res','ver_res','preguntasChats','idPregunta'));
    }
}
