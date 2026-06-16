<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;


class UsuarioController extends Controller
{   
    public function index()
    {
        $relacionEloquent = 'roles';
        $usuarios = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '!=', 'Solicitante');
        })
        ->get();
        return view('usuarios.index',compact('usuarios'));
    }

    public function create()
    {
        //Vamos a traer un usuario para asignarle los roles
        $roles = Role::pluck('name','name')->all();
        return view('usuarios.crear', compact('roles'));
    }

    public function store(Request $request)
    {        
        $data = $request->all();

        //Validar documentacion
        request()->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email,',
            'password'  => 'required|same:confirm-password',
            'roles'     => 'required',
            'delegacion'=> 'required',
            'type'      => 'required',
            'profile_photo_path'      => 'required' 
        ], $data);


        $input = $request->all();
        //Hacemos un hash del campo que tiene el password
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        //Documentación de spatie para asignar roles
        $user->assignRole($request->input('roles'));

        return redirect()->route('usuarios');

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $user = User::find($id)->delete();
        //$usuarios = User::paginate(10);
        //return view('usuarios.index',compact('usuarios'));
        return redirect()->route('usuarios');
    }
}
