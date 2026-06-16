<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Persona;
use App\Models\CapacitacionPersona;
use App\Models\Documentos;
use App\Models\Modulo;
use App\Models\Capacitacion;
use App\Models\CapacitacionEncuesta;
use App\Models\Calificacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

//Para sacar el Id del usuario
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class MiscapacitacionController extends Controller
{   
    function __contruct()
    {
        $this->middleware('permission:ver-persona | crear-persona | editar-persona | borrar-persona', ['only'=>['index']]);
        $this->middleware('permission:crear-persona', ['only'=>['create','store']]);
        $this->middleware('permission:editar-persona',['only'=>['edit','update']]);
        $this->middleware('permission:borrar-persona',['only'=>['destroy']]);
    }

    public function index()
    {
        $id = auth()->user()->id;
        $persona = Persona::where('id_usuario', $id)->first();
        if($persona != null){
            $capacitaciones = DB::table('capacitaciones')
            ->join('capacitaciones_persona', 'capacitaciones_persona.capacitacion', '=', 'capacitaciones.id')

            ->leftJoin('capacitaciones_calificacion', function ($join) {
                $join
                    ->on('capacitaciones_calificacion.capacitacion', '=', 'capacitaciones_persona.capacitacion')
                    ->on('capacitaciones_calificacion.persona', '=', 'capacitaciones_persona.persona');
            })

            ->select('capacitaciones.id', 'capacitaciones.nombre', 'capacitaciones.estatus', 'capacitaciones_calificacion.calificacion')
            ->where('capacitaciones_persona.persona', $persona->id)
            ->groupBy('capacitaciones.id')
            ->paginate(10);
        }
        else{
            $capacitaciones = [];
        }
        return view('miscapacitaciones.index', compact('capacitaciones'));
    }

    public function create()
    {
        $id = auth()->user()->id;
        $usuario = User::find($id);
        $persona = Persona::where('id_usuario', $id)->first();
        return view('miscapacitaciones.crear', compact('usuario','persona'));
    }

    public function store(Request $request)
    {
        $id = auth()->user()->id;
        $data = $request->all();
        $data_doc = [];
        $data_doc['id_usuario'] = $id;

        //Validar documentacion
        request()->validate([
            'nombre'                    => 'required',
            'email'                     => 'required',
            'cargo'                     => 'required',
            'area_adcripcion'           => 'required',
            'telefono'                  => 'required|digits:10',
            'tilulo_universitario'      => 'required',
            'documentoTitulo'           => 'nullable',
            'estudio_maximo'            => 'required',
            'documentoEstudios'         => 'nullable',
            'especialidades'            => 'nullable',
            'documentoEspecialidades'   => 'nullable',
            'diplomados'                => 'nullable',
            'documentoDiplomado'        => 'nullable',
            'seminarios'                => 'nullable',
            'documentoSeminario'        => 'nullable',
            'cursos'                    => 'nullable',
            'documentoCursos'           => 'nullable',
            'acciones_desarrollo'       => 'nullable',
            'documentoDesarrollo'       => 'nullable',
        ], $data);
        
        $data['id_usuario'] = $id;
        
        //Validar que ya existe registro
        $persona = Persona::where(['id_usuario' => $id])->first();

        //Si no existe se va registro
        if($persona == null){
            //documento de titulo
            $nombretitulo = $data["nombre"]."_Titulo.pdf";
            $path = Storage::putFileAs(
                'documentos_personal', $request->file('documentoTitulo'), $nombretitulo
            );
            $data_doc['titulo'] = $nombretitulo;


            //documento de Estudios
            $nombreestudios = $data["nombre"]."_Estudios.pdf";
            $path = Storage::putFileAs(
                'documentos_personal', $request->file('documentoEstudios'), $nombreestudios
            );
            $data_doc['nivel_estudios'] = $nombreestudios;



            //documento de Especialidades si lo selecciona
            if(isset($data["documentoEspecialidades"])){
                $nombreespecialidades = $data["nombre"]."_Especialidades.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoEspecialidades'), $nombreespecialidades
                );
                $data_doc['especialidad'] = $nombreespecialidades;
            }


            //documento de diplomado
            if(isset($data["documentoDiplomado"])){
                $nombrediplomado = $data["nombre"]."_Diplomados.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoDiplomado'), $nombrediplomado
                );
                $data_doc['diplomado'] = $nombrediplomado;
            }


            if(isset($data["documentoSeminario"])){
                $nombreseminario = $data["nombre"]."_Diplomados.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoSeminario'), $nombreseminario
                );
                $data_doc['seminario'] = $nombreseminario;
            }


            if(isset($data["documentoCursos"])){
                $nombrecursos = $data["nombre"]."_Cursos.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoCursos'), $nombrecursos
                );
                $data_doc['cursos'] = $nombrecursos;
            }


            if(isset($data["documentoDesarrollo"])){
                $nombredesarrollo = $data["nombre"]."_Desarrollo.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoDesarrollo'), $nombredesarrollo
                );
                $data_doc['desarrollo'] = $nombredesarrollo;
            }

            //dd($data);
            Persona::create($data);
            Documentos::create($data_doc);  
            return redirect()->route('miscapacitaciones.index')->with('success', 'Datos actualizados correctamente.'); 
        }
        //Si ya existe se va actualizar
        else{
            //documento de titulo
            if(isset($data["documentoTitulo"])){
                $nombretitulo = $data["nombre"]."_Titulo.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoTitulo'), $nombretitulo
                );
                $data_doc['titulo'] = $nombretitulo;
            }


            //documento de Estudios
            if(isset($data["documentoEstudios"])){
                $nombreestudios = $data["nombre"]."_Estudios.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoEstudios'), $nombreestudios
                );
                $data_doc['nivel_estudios'] = $nombreestudios;
            }


            //documento de Especialidades si lo selecciona
            if(isset($data["documentoEspecialidades"])){
                $nombreespecialidades = $data["nombre"]."_Especialidades.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoEspecialidades'), $nombreespecialidades
                );
                $data_doc['especialidad'] = $nombreespecialidades;
            }


            //documento de diplomado
            if(isset($data["documentoDiplomado"])){
                $nombrediplomado = $data["nombre"]."_Diplomados.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoDiplomado'), $nombrediplomado
                );
                $data_doc['diplomado'] = $nombrediplomado;
            }


            if(isset($data["documentoSeminario"])){
                $nombreseminario = $data["nombre"]."_Diplomados.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoSeminario'), $nombreseminario
                );
                $data_doc['seminario'] = $nombreseminario;
            }


            if(isset($data["documentoCursos"])){
                $nombrecursos = $data["nombre"]."_Cursos.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoCursos'), $nombrecursos
                );
                $data_doc['cursos'] = $nombrecursos;
            }


            if(isset($data["documentoDesarrollo"])){
                $nombredesarrollo = $data["nombre"]."_Desarrollo.pdf";
                $path = Storage::putFileAs(
                    'documentos_personal', $request->file('documentoDesarrollo'), $nombredesarrollo
                );
                $data_doc['desarrollo'] = $nombredesarrollo;
            }


            $capacitacion = Persona::where('id_usuario', $id)->first();
            $capacitacion->update($data);

            $documentos = Documentos::where('id_usuario', $id)->first();
            $documentos->update($data_doc);  
            return redirect()->route('miscapacitaciones.index')->with('success', 'Datos actualizados correctamente.'); 
        }
    }

    public function edit($id)
    {
        //aqui va regresar la lista de modulos de esa capacitacion, va bloquear los que ya termino y solo puede tener uno activo a las vez y los que ya se terminaron va aparecer su calificacion
        $id_usuario = auth()->user()->id;
        $persona = Persona::where('id_usuario', $id_usuario)->first();
        $capacitacion = Capacitacion::find($id);
        $modulos = DB::table('capacitaciones_persona')
        ->join('capacitaciones_modulo', function ($join) {
            $join
                //->on('capacitaciones_modulo.capacitacion', '=', 'capacitaciones_persona.capacitacion')
                ->on('capacitaciones_modulo.id_cap', '=', 'capacitaciones_persona.capacitacion')
                ->on('capacitaciones_modulo.id_modulo', '=', 'capacitaciones_persona.modulo');
        })
        ->select('capacitaciones_modulo.id_modulo','capacitaciones_modulo.nombre', 'capacitaciones_persona.estatus', 'capacitaciones_persona.calificacion')
        ->where('capacitaciones_persona.persona', $persona->id)
        ->where('capacitaciones_persona.capacitacion', $id)
        ->get();

        return view('miscapacitaciones.modulos', compact('capacitacion','modulos'));
    }

    public function iniciar($id,$mod)
    {
        $capacitacion = Capacitacion::find($id);
        $modulos = Modulo::where(['id_cap' => $id, 'id_modulo' => $mod])->first();
        return view('miscapacitaciones.iniciar', compact('capacitacion','modulos'));
    }

    public function evaluacion($id,$mod)
    {
        //Actualizar el estatus
        $id_usuario = auth()->user()->id;
        $persona = Persona::where('id_usuario', $id_usuario)->first();

        $persona_update = DB::table('capacitaciones_persona')
            ->where('persona', $persona->id)
            ->where('modulo', $mod)
            ->where('capacitacion', $id)
            ->update(['estatus' => 'En prueba']);

        $capacitacion = Capacitacion::where('id', $id)->first();
        $modulos = Modulo::where('id_cap', $id)->get();
        $encuestas = CapacitacionEncuesta::where(['id_cap' => $id, 'id_modulo' => $mod])->get();
        $estatus   = CapacitacionPersona::where (['capacitacion' => $id, 'persona' => $persona->id, 'modulo' => $mod])->first();
        
        $estatus = $estatus->estatus;
        return view('miscapacitaciones.evaluacion', compact('capacitacion','mod','encuestas','estatus'));
    }

    public function guardar_respuestas(Request $request){
        $id_usuario = auth()->user()->id;
        $persona = Persona::where('id_usuario', $id_usuario)->first();
        $encuestas = CapacitacionEncuesta::where(['id_cap' => $request->cap, 'id_modulo' => $request->mod])->get();

        $numero_respuesta = 0;
        $calificacion = 0;
        
        // Voy a leer todas las encuestas para calificar
        foreach($encuestas as $encuesta){
            $respueta = "respuesta".$numero_respuesta;
            $resp = $request->$respueta;

            if($resp == $encuesta->correcta){
                $calificacion ++;
            }
            $numero_respuesta++;
        }
        $calif = ($calificacion / $numero_respuesta) * 100;

        $persona_update = DB::table('capacitaciones_persona')
            ->where('persona', $persona->id)
            ->where('modulo', $request->mod)
            ->where('capacitacion', $request->cap)
            ->update([ 
                'calificacion' => $calif,
                'estatus' => 'Terminado'
            ]);

        //obtengo la suma y el total
        $sum     = CapacitacionPersona::where(['persona' => $persona->id, 'capacitacion' => $request->cap])->sum('calificacion');
        $modulos = CapacitacionPersona::where(['persona' => $persona->id, 'capacitacion' => $request->cap])->count();

        $porcentaje = $sum / $modulos;
        
        //Actualizar la calificacion
        $existe_calificacion = Calificacion::where(['persona' => $persona->id, 'capacitacion' => $request->cap])->first();
        
        //Voy a insertar
        if($existe_calificacion == null){
            $data_calificacion = [
                'capacitacion'  => $request->cap,
                'persona'       => $persona->id,
                'calificacion'  => $porcentaje,
            ];
            Calificacion::create($data_calificacion);
        }
        //Voy actualizar
        else{
            $data_calificacion = [
                'calificacion' => $porcentaje,
            ];
            $calificacion = Calificacion::where(['persona' => $persona->id, 'capacitacion' => $request->cap])->first();
            $calificacion->update($data_calificacion);
        }

        $capacitacion = Capacitacion::find($request->cap);
        $modulos = DB::table('capacitaciones_persona')
        ->join('capacitaciones_modulo', function ($join) {
            $join
                ->on('capacitaciones_modulo.id_cap', '=', 'capacitaciones_persona.capacitacion')
                ->on('capacitaciones_modulo.id_modulo', '=', 'capacitaciones_persona.modulo');
        })
        ->select('capacitaciones_modulo.id_modulo','capacitaciones_modulo.nombre', 'capacitaciones_persona.estatus', 'capacitaciones_persona.calificacion')
        ->where('capacitaciones_persona.persona', $persona->id)
        ->get();
    
        return redirect()->route('miscapacitaciones')->with('success', 'Tu calificación es '.$calif.'.');
        //return view('miscapacitaciones.modulos', compact('capacitacion','modulos'));
    }
}