<?php

namespace App\Http\Controllers;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        addVendors(['amcharts', 'amcharts-maps', 'amcharts-stock']);
        
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $delegacion = $user->delegacion;
        $relacionEloquent = "roles";


        if($userRole[0] == "Super Usuario" || $userRole[0] == "Administrador" || $userRole[0] == "Estadistica"){
            $sedes = ["Morelia", "Zitácuaro","Uruapan", "Lázaro Cárdenas","Zamora", "Sahuayo"];
            $conciliadores = User::whereHas($relacionEloquent, function ($query) {
                    return $query->where('name', '=', 'Conciliador');
                })
                ->get();
        }
        //puede ver las sede y conciliadores
        elseif($userRole[0] == "Delegado" || $userRole[0] == "Enlace"){
            if($delegacion == "Morelia" || $delegacion == "Zitácuaro"){
                $sedes = ["Morelia", "Zitácuaro"];
                $conciliadores = User::whereHas($relacionEloquent, function ($query) {
                    return $query->where('name', '=', 'Conciliador');
                })
                ->where('delegacion', $delegacion)
                ->get();
            }
            else if($delegacion == "Uruapan" || $delegacion == "Lázaro Cárdenas"){
                $sedes = ["Uruapan", "Lázaro Cárdenas"];
                $conciliadores = User::whereHas($relacionEloquent, function ($query) {
                    return $query->where('name', '=', 'Conciliador');
                })
                ->where('delegacion', $delegacion)
                ->get();
            }
            else if($delegacion == "Zamora" || $delegacion == "Sahuayo"){
                $sedes = ["Zamora", "Sahuayo"];
                $conciliadores = User::whereHas($relacionEloquent, function ($query) {
                    return $query->where('name', '=', 'Conciliador');
                })
                ->where('delegacion', $delegacion)
                ->get();
            }
        }
        //puede ver unicamente las sede
        else{
            if($delegacion == "Morelia" || $delegacion == "Zitácuaro"){
                $sedes = ["Morelia", "Zitácuaro"];
            }
            else if($delegacion == "Uruapan" || $delegacion == "Lázaro Cárdenas"){
                $sedes = ["Uruapan", "Lázaro Cárdenas"];
            }
            else if($delegacion == "Zamora" || $delegacion == "Sahuayo"){
                $sedes = ["Zamora", "Sahuayo"];
            }
            $conciliadores = User::whereHas($relacionEloquent, function ($query) {
                return $query->where('name', '=', 'Conciliador');
            })
            ->where('id', $id_usuario)
            ->get();
        }
        return view('pages/dashboards.index', compact('userRole','sedes','conciliadores'));
    }
}
