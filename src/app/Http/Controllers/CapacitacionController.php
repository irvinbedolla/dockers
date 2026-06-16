<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Capacitacion;
use App\Models\Persona;
use App\Models\Documentos;
use App\Models\Modulo;
use App\Models\CapacitacionEncuesta;
use App\Models\CapacitacionPersona;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

//Para sacar el Id del usuario
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class CapacitacionController extends Controller
{
    public function index()
    {
        //Paginar las personas
        $capacitaciones = Capacitacion::all();
        return view('capacitaciones.index', compact('capacitaciones'));
    }

    public function create()
    {
        $id_usuario = Auth::id();
        return view('capacitaciones.crear', compact('id_usuario'));
    }

    public function crear_capacitacion(Request $request)
    {
        $this->validate($request, [
            'nombre'  => 'required',
            'modulos' => 'required|numeric|max:4',
            'inicio'  => 'required|date',
            'fin'     => 'required|date',
        ]);

        $data = $request->all();
        $capacitaciones = Capacitacion::create($data);

        return redirect()->route('capacitaciones');
    }

    public function show($id)
    {
        $capacitacion = Capacitacion::find($id);
        return view('capacitaciones.editar_curso', compact('capacitacion'));
    }

    public function edit($id)
    {
        $persona = Persona::where('id_usuario', $id)
              ->update(['estatus' => "Aceptado"]);

        return redirect()->route('capacitaciones.personas');
    }


    public function destroy($id)
    {
        $cap = Capacitacion::find($id)->delete();
        $modulo   = Modulo::where('id_cap', $id)->delete();
        $encuesta = CapacitacionEncuesta::where('id_cap', $id)->delete();
        return redirect()->route('capacitaciones');
    }

    public function personas(){
        $personas = Persona::where('estatus', 'Pendiente')->get();
        return view('capacitaciones.personas', compact('personas'));
    }

    public function personas_documentos($id){
        $documentos = Documentos::where('id_usuario', $id)->get();
        return view('capacitaciones.documentos', compact('documentos'));
    }

    public function modulos($id){
        $capacitacion = Capacitacion::find($id);
        $modulos = Modulo::where('id_cap', $id)->get();
        return view('capacitaciones.index_modulo', compact('capacitacion','modulos'));
    }

    public function crear_modulo($id){
        $capacitacion = Capacitacion::find($id);
        return view('capacitaciones.crear_modulo', compact('capacitacion'));
    }

    public function guardar_modulo(Request $request){
        $data = $request->all();
        //dd($data);
        $modulo = Modulo::where('id_cap', $data["cap"])->first();
        if($modulo == null){
            $modulo_insetar = 1;
        }
        else{
            $modulo_insetar = $modulo["id_modulo"];
            $modulo_insetar++;
        }

        request()->validate([
            'nombre'          => 'required',
            'introduccion'    => 'required',
            'desarrollo'      => 'required',
        ], $data);
        
        $anexo1= null;
        $anexo2= null;
        $anexo3= null;
        $anexo4= null;
        $anexo5= null;


        //Anexo 1
        if(isset($data["anexo1"])){
            $nombreAnexo = $data["nombre"]."_Anexo1.pdf";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo1'), $nombreAnexo
            );
            $anexo1 = $nombreAnexo;
        }

        //Anexo 2
        if(isset($data["anexo2"])){
            $nombreAnexo = $data["nombre"]."_Anexo2.pdf";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo2'), $nombreAnexo
            );
            $anexo2 = $nombreAnexo;
        }

        //Anexo 3
        if(isset($data["anexo3"])){
            $nombreAnexo = $data["nombre"]."_Anexo3.pdf";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo3'), $nombreAnexo
            );
            $anexo3 = $nombreAnexo;
        }

        //Anexo 4
        if(isset($data["anexo4"])){
            $nombreAnexo = $data["nombre"]."_Anexo4.mp4";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo4'), $nombreAnexo
            );
            $anexo4 = $nombreAnexo;
        }

        //Anexo 5
        if(isset($data["anexo5"])){
            $nombreAnexo = $data["nombre"]."_Anexo5.mp4";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo5'), $nombreAnexo
            );
            $anexo5 = $nombreAnexo;
        }

        $data_insertar = [
            'id_cap'          => $data["cap"],
            'id_modulo'       => $modulo_insetar,
            'nombre'          => $data["nombre"],
            'introduccion'    => $data["introduccion"],
            'desarrollo'      => $data["desarrollo"],
            'anexo1'          => $anexo1,
            'anexo2'          => $anexo2,
            'anexo3'          => $anexo3,
            'anexo4'          => $anexo4,
            'anexo5'          => $anexo5,
        ];

        $modulo = Modulo::create($data_insertar);
        
        //saco el numero de elementos
        if(isset($data["pregunta"])){
            $longitud = count($data["pregunta"]);

            for($i = 0; $i < count($data["pregunta"]); $i++){
                $data_respuestas = [
                    'id_cap'          => $data["cap"],
                    'id_modulo'       => $modulo_insetar,
                    'pregunta'        => $data["pregunta"][$i]."",
                    'respuesta1'      => $data["respuesta1"][$i]."",
                    'respuesta2'      => $data["respuesta2"][$i]."",
                    'respuesta3'      => $data["respuesta3"][$i]."",
                    'respuesta4'      => $data["respuesta4"][$i]."",
                    'correcta'        => $data["correcta"][$i]."",
                ];
                $respuestas = CapacitacionEncuesta::create($data_respuestas);
            }
        }

        return redirect()->route('capacitaciones');
    }

    public function borrar_modulo($id,$mod)
    {
        //borra el modulo y encuentas
        $modulo   = Modulo::where(['id_cap' => $id, 'id_modulo' => $mod])->delete();
        $encuesta = CapacitacionEncuesta::where(['id_cap' => $id, 'id_modulo' => $mod])->delete();
        $capacitacion = Capacitacion::find($id);
        $modulos = Modulo::where('id_cap', $id)->get();

        return redirect()->route('capacitaciones');
    }

    public function editar_modulo($id){
        $modulo = Modulo::find($id);
        return view('capacitaciones.editar_modulo', compact('modulo'));
    }

    public function guardar_modulo_editar(Request $request){
        $data = $request->all();
        $modulo = Modulo::find($data["id"]);
       
        request()->validate([
            'nombre'          => 'required',
            'introduccion'    => 'required',
            'desarrollo'      => 'required',
        ], $data);
        
        $data_update = [
            'nombre'          => $data["nombre"],
            'introduccion'    => $data["introduccion"],
            'desarrollo'      => $data["desarrollo"],
            'anexo1'          => null,  
            'anexo2'          => null,
            'anexo3'          => null,
            'anexo4'          => null,
            'anexo5'          => null,
        ];

    
        //Anexo 1
        if(isset($data["anexo1"])){
            $nombreAnexo1 = $data["cap"]."-".$data["id"]."-".$data["nombre"]."_Anexo1.pdf";
            if($modulo->anexo1 != null){
                unlink(storage_path('app/documentos_modulo/'.$modulo->anexo1));
            }
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo1'), $nombreAnexo1
            );
            $data_update['anexo1'] = $nombreAnexo1;
        }

        //Anexo 2
        if(isset($data["anexo2"])){
            $nombreAnexo2 = $data["cap"]."-".$data["id"]."-".$data["nombre"]."_Anexo2.pdf";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo2'), $nombreAnexo2
            );
            $data_update['anexo2'] = $nombreAnexo2;
        }

        //Anexo 3
        if(isset($data["anexo3"])){
            $nombreAnexo3 = $data["cap"]."-".$data["id"]."-".$data["nombre"]."_Anexo3.pdf";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo3'), $nombreAnexo3
            );
            $data_update['anexo3'] = $nombreAnexo3;
        }

        //Anexo 4
        if(isset($data["anexo4"])){
            $nombreAnexo4 = $data["cap"]."-".$data["id"]."-".$data["nombre"]."_Anexo4.mp4";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo4'), $nombreAnexo4
            );
            $data_update['anexo4'] = $nombreAnexo4;
        }

        //Anexo 5
        if(isset($data["anexo5"])){
            $nombreAnexo5 = $data["cap"]."-".$data["id"]."-".$data["nombre"]."_Anexo5.mp4";
            $path = Storage::putFileAs(
                'documentos_modulo', $request->file('anexo5'), $nombreAnexo5
            );
            $data_update['anexo5'] = $nombreAnexo5;
        }

        $modulo = DB::table('capacitaciones_modulo')
            ->where('id', $data["id"])
            ->update(['nombre' => $data["nombre"],'introduccion' => $data["introduccion"],'desarrollo' => $data["desarrollo"],'anexo1' => $data_update["anexo1"], 'anexo2' => $data_update["anexo2"],
            'anexo3' => $data_update["anexo3"], 'anexo4' => $data_update["anexo4"],'anexo5' => $data_update["anexo5"]]);


        //$modulo->update($data_update);
        
        $capacitacion = Capacitacion::find($data["cap"]);
        $modulos = Modulo::where('id_cap', $data["cap"])->get();

        //return view('capacitaciones.index_modulo', compact('capacitacion','modulos'));
        return redirect()->route('capacitaciones');
    }

    public function editar_encuesta($id,$mod){
        //id es el id de la capaciotacion
        //mod es el id de modulo
        $modulo = Modulo::where( ['id_cap' => $id, 'id_modulo' => $mod] )->first();
        $encuestas = CapacitacionEncuesta::where( ['id_cap' => $id, 'id_modulo' => $mod] )->get();
        return view('capacitaciones.editar_encuesta', compact('encuestas','modulo'));
    }
    
    public function guardar_encuesta_editar(Request $request){
        $data = $request->all();
        //saco el numero de elementos
        $longitud = count($data["pregunta"]);
        $modulo_borrar = CapacitacionEncuesta::where(['id_cap' => $data["cot"], 'id_modulo' => $data["mod"]])->delete();
        

        for($i = 0; $i < count($data["pregunta"]); $i++){
            $data_respuestas = [
                'id_cap'          => $data["cot"],
                'id_modulo'       => $data["mod"],
                'pregunta'        => $data["pregunta"][$i]."",
                'respuesta1'      => $data["respuesta1"][$i]."",
                'respuesta2'      => $data["respuesta2"][$i]."",
                'respuesta3'      => $data["respuesta3"][$i]."",
                'respuesta4'      => $data["respuesta4"][$i]."",
                'correcta'        => $data["correcta"][$i]."",
            ];
            $respuestas = CapacitacionEncuesta::create($data_respuestas);
        }

        return redirect()->route('capacitaciones');
    }

    public function agregar_personas($id){
        //Revisar esta funcion
        $capacitacion = $id;
        
        $personas_aceptadas = Persona::join('capacitaciones_persona', 'capacitaciones_persona.persona', '=', 'persona.id')
        ->select('persona.id', 'persona.id_usuario', 'persona.nombre', 'persona.cargo', 'persona.area_adcripcion')
        ->where('capacitaciones_persona.capacitacion', $id)
        ->groupBy('capacitaciones_persona.persona')
        ->get();

        $personas = Persona::where('estatus', 'Aceptado')->get();
        return view('capacitaciones.personas_agregar', compact('capacitacion','personas','personas_aceptadas'));
    }

    public function persona_incluir($cap, $per){
        $modulos = Modulo::where('id_cap', $cap)->get();   
        //Borrar esto
        $id = $cap;
        foreach($modulos as $mod){
            $data = [
                'capacitacion' => $cap,
                'persona'      => $per,
                'modulo'       => $mod->id_modulo,
                'estatus'      => 'En curso',
            ];
            CapacitacionPersona::create($data);
        }

        $personas_aceptadas = Persona::join('capacitaciones_persona', 'capacitaciones_persona.persona', '=', 'persona.id')
        ->select('persona.id', 'persona.id_usuario', 'persona.nombre', 'persona.cargo', 'persona.area_adcripcion')
        ->where('capacitaciones_persona.capacitacion', $cap)
        ->groupBy('persona.id')->get();
        $personas = Persona::where('estatus', 'Aceptado')->get();

        return redirect()->route('capacitaciones.addpersonas', compact('id'));
    }

    public function persona_quitar($cap, $per){
        $id = $cap;
        $modulo_borrar = CapacitacionPersona::where(['capacitacion' => $cap, 'persona' => $per])->delete();

        $personas_aceptadas = persona::join('capacitaciones_persona', 'capacitaciones_persona.persona', '=', 'persona.id')
        ->select('persona.id', 'persona.id_usuario', 'persona.nombre', 'persona.cargo', 'persona.area_adcripcion')
        ->where('capacitaciones_persona.capacitacion', $cap)
        ->groupBy('capacitaciones_persona.persona')
        ->get();

        //return redirect()->route('capacitaciones');
        return redirect()->route('capacitaciones.addpersonas', compact('id'));
    }

    public function personas_calificacion($id){
        //Personas que estan en ese curso y susu calificaciones

        $capacitaciones = DB::table('capacitaciones')
            ->join('capacitaciones_modulo', 'capacitaciones_modulo.id_cap', '=', 'capacitaciones.id')
            ->join('capacitaciones_persona', function ($join_persona) {
                $join_persona
                    ->on('capacitaciones_persona.capacitacion', '=', 'capacitaciones.id')
                    ->on('capacitaciones_modulo.id_modulo', '=', 'capacitaciones_persona.modulo');
            })
            ->join('persona', 'persona.id', '=', 'capacitaciones_persona.persona')
            ->leftJoin('capacitaciones_calificacion', function ($join) {
                $join
                    ->on('capacitaciones_calificacion.capacitacion', '=', 'capacitaciones.id')
                    ->on('capacitaciones_calificacion.persona', '=', 'persona.id');
            })
            ->select('capacitaciones.id', 'capacitaciones.nombre as capacitacion', 'capacitaciones_modulo.nombre as modulo', 'persona.nombre as persona', 'capacitaciones_calificacion.calificacion' )
            ->where('capacitaciones.id', $id)

            ->get();
        return view('capacitaciones.calificaciones', compact('capacitaciones'));
    }

    public function terminar($id){
        $capacitacion = Capacitacion::find($id);
        $data_update = [
            'estatus'          => 'Terminado',
        ];
        $capacitacion->update($data_update);
        
        return redirect()->route('capacitaciones');
    }
}
