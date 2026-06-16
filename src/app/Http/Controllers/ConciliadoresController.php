<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//agregamos
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PermisosConciliador;

class ConciliadoresController extends Controller
{
    public function index()
    {
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        
        if($userRole[0] == "Super Usuario"){
            $conciliadores = User::whereHas('roles', function ($query) {
                return $query->where('name', '=', 'Conciliador');
            })
            ->get();
        }else{
            $conciliadores = User::whereHas('roles', function ($query) {
                return $query->where('name', '=', 'Conciliador');
            })
            ->where('delegacion', $user["delegacion"])
            ->get();
        }

        return view('conciliadores.index',compact('conciliadores'));
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $permisos = PermisosConciliador::where('id_conciliador',$data["id"])->get();
        if($data["Lunes"] == "on"){
            $lunes = "Si";
        }
        else{
            $lunes = "No";
        }
        if($data["Martes"] == "on"){
            $martes = "Si";
        }
        else{
            $martes = "No";
        }
        if($data["Miercoles"] == "on"){
            $miercoles = "Si";
        }
        else{
            $miercoles = "No";
        }
        if($data["Jueves"] == "on"){
            $jueves = "Si";
        }
        else{
            $jueves = "No";
        }
        if($data["Viernes"] == "on"){
            $viernes = "Si";
        }
        else{
            $viernes = "No";
        }
        if(isset($data["horario_lunes_inicio"])){
            $lunes_inicio =  $data["horario_lunes_inicio"];
        }
        else{
            $lunes_inicio = "";        
        }
        if(isset($data["horario_lunes_final"])){
            $lunes_final =  $data["horario_lunes_final"];
        }
        else{
            $lunes_final = "";
        }
        if(isset($data["horario_martes_inicio"])){
            $martes_inicio =  $data["horario_martes_inicio"];
        }
        else{
            $martes_inicio = "";
        }
        if(isset($data["horario_martes_final"])){
            $martes_final =  $data["horario_martes_final"];
        }
        else{
            $martes_final = "";
        }
        if(isset($data["horario_miercoles_inicio"])){
            $miercoles_inicio =  $data["horario_miercoles_inicio"];
        }
        else{
            $miercoles_inicio = "";
        }
        if(isset($data["horario_miercoles_final"])){
            $miercoles_final =  $data["horario_miercoles_final"];
        }
        else{
            $miercoles_final = "";
        }
        if(isset($data["horario_jueves_inicio"])){
            $jueves_inicio =  $data["horario_jueves_inicio"];
        }
        else{
            $jueves_inicio = "";
        }
        if(isset($data["horario_jueves_final"])){
            $jueves_final =  $data["horario_jueves_final"];
        }
        else{
            $jueves_final = "";
        }
        if(isset($data["horario_viernes_inicio"])){
            $viernes_inicio =  $data["horario_viernes_inicio"];
        }
        else{
            $viernes_inicio = "";
        }
        if(isset($data["horario_viernes_final"])){
            $viernes_final =  $data["horario_viernes_final"];
        }
        else{
            $viernes_final = "";
        }
        
        //Si no exite voy a insertar
        if(count($permisos) == 0){
            $data_insert = [
                'id_conciliador'    => $data["id"],
                'tipo'              => $data["tipo"],
                'lunes'             => $lunes,
                'martes'            => $martes,
                'miercoles'         => $miercoles,
                'jueves'            => $jueves,
                'viernes'           => $viernes,
                'lunes_inicio'      => $lunes_inicio,
                'lunes_final'       => $lunes_final,
                'martes_inicio'     => $martes_inicio,
                'martes_final'      => $martes_final,
                'miercoles_inicio'  => $miercoles_inicio,
                'miercoles_final'   => $miercoles_final,
                'jueves_inicio'     => $jueves_inicio,
                'jueves_final'      => $jueves_final,
                'viernes_inicio'    => $viernes_inicio,
                'viernes_final'     => $viernes_final,
            ];

            PermisosConciliador::create($data_insert);  
        }
        else{
            PermisosConciliador::where('id_conciliador',$data["id"])
            ->update([
                'id_conciliador'    => $data["id"],
                'tipo'              => $data["tipo"],
                'lunes'             => $lunes,
                'martes'            => $martes,
                'miercoles'         => $miercoles,
                'jueves'            => $jueves,
                'viernes'           => $viernes,
                'lunes_inicio'      => $lunes_inicio,
                'lunes_final'       => $lunes_final,
                'martes_inicio'     => $martes_inicio,
                'martes_final'      => $martes_final,
                'miercoles_inicio'  => $miercoles_inicio,
                'miercoles_final'   => $miercoles_final,
                'jueves_inicio'     => $jueves_inicio,
                'jueves_final'      => $jueves_final,
                'viernes_inicio'    => $viernes_inicio,
                'viernes_final'     => $viernes_final,
            ]);
        }
        return back()->with('success', 'Horarios Actualizado Correctamente.'); 
    }
}
