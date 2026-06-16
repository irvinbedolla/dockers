<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Models\CitaDireccion;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CitaDireccionController extends Controller
{   
    public function index()
    {
        $citas = CitaDireccion::orderBy('created_at', 'desc')->limit(500)->get();
        return view('citas.index',compact('citas'));
    }

    public function create()
    {
        return view('citas.create');
    }

    public function cita_direccion_guardar(Request $request)
    {        
        $data = $request->all();

        //Validar documentacion
        request()->validate([
            'nombre'        => 'required',
            'descripcion'   => 'required',
            'fecha'         => 'required',
            'horaInicial'   => 'required',
            'horaFinal'     => 'required',
            'UD'            => 'required'
        ], $data);

        $data_insertar = array(
            'nombre'        => $data["nombre"],
            'descripcion'   => $data["descripcion"],
            'fecha'         => $data["fecha"],
            'hora'          => $data["horaInicial"],
            'fin'           => $data["horaFinal"],
            'estatus'       => "Pendiente",
            'delegacion'    => "Morelia",
            'unidad'        => $data["UD"],
        );
        CitaDireccion::create($data_insertar);
        
        return redirect()->route('indexDireccionGeneral');

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //Se invocan los dos modelos User y rol
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->first();
        
        return view('usuarios.editar', compact('user','roles','userRole'));
    }

    public function update(Request $request, $id)
    {
        //Primero se hace la validación como en store
        $this->validate($request, [
            'name' => 'required',
            //Se guarda el campo email y se pide que sea de tipo email y único y se agrega el id
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required',
            'delegacion' => 'required',
            'type' => 'required' 
        ]);

        //Hacemos un condicional sobre los inputs que tenemos
        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }else {
            $input = Arr::except($input, array('password'));
        }
        
        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));
        return redirect()->route('usuarios');
    }

    public function generarQr($id)
    {
        $dataParaQr = "https://siconcilio.cclmichoacan.gob.mx/Confirmacion/$id";
        // Genera el código QR como una cadena SVG (Scalable Vector Graphics)
        $qrCode = QrCode::size(200)->generate($dataParaQr);

        return view('qr.mostrar', compact('qrCode'));
    }

    public function destroy($id)
    {
        //
        $user = User::find($id)->delete();
        //$usuarios = User::paginate(10);
        //return view('usuarios.index',compact('usuarios'));
        return redirect()->route('usuarios');
    }

    public function codigoQR($id){
        $citas = CitaDireccion::find($id);
        return view('vistaQR_cita',compact('citas'));
    }
}
