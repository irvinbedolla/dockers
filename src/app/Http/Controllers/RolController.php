<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//agregamos
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;


class RolController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permission = Permission::get();
        return view('roles.crear', compact('permission'));
    }

    public function store_rol(Request $request)
    {
        $check_Role = Role::where(['name'=>$request->name])->first();
        //Si no esta vacia
        if (!empty($check_Role)){
            session()->flash('before_before');
            return  redirect()->route('roles.index');
        }
        //Si esta vacio lo va agregar
        else{
            $permissions = [];
            $post_permissions = $request->input('permission');
            //$this->validate($request, ['name' => 'required', 'permission' => 'required']);
            $role = Role::create(['name' => $request->input('name')]);
            foreach ($post_permissions as $key => $val) {
                $permissions[intval($val)] = intval($val);
            }   
            //    $role->givePermissionTo(['create posts', 'edit posts', 'delete posts']);
            $role->syncPermissions($permissions);
            session()->flash('add_data');
            return redirect()->route('roles');
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermission = DB::table('role_has_permissions')->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
        return view('roles.editar', compact('role', 'permission', 'rolePermission'));
            
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
        
        $permissions = [];
        $post_permissions = $request->input('permission');
        $this->validate($request, ['name' => 'required', 'permission' => 'required']);
            
        foreach ($post_permissions as $key => $val) {
            $permissions[intval($val)] = intval($val);
        }            
        $role->syncPermissions($permissions);
        return redirect()->route('roles');
    }

    public function destroy($id)
    {
        //
        DB::table('roles')->where('id', $id)-> delete();
        return redirect()->route('roles');
    }
}
