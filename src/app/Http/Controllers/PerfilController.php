<?php

namespace App\Http\Controllers;

use App\Support\FotoPerfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Pantalla "Mi perfil": lo poco que cada quien puede cambiar de su cuenta.
 *
 * Sustituye a /cambio-contrasena, que sólo hacía la contraseña. Nombre, correo
 * y rol se muestran de solo lectura: quien los cambia es un Super Usuario
 * desde Administración → Usuarios, porque el correo es la llave del login y el
 * rol define los permisos. Aquí no hay forma de tocarlos, ni siquiera mandando
 * el campo a mano: los dos métodos leen del request nada más lo suyo.
 */
class PerfilController extends Controller
{
    /** Lo que se le pide a una contraseña nueva. Se le dice al usuario. */
    public const MINIMO_CONTRASENA = 8;

    public function index()
    {
        return view('perfil.index', ['usuario' => auth()->user()]);
    }

    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192|dimensions:min_width=200,min_height=200',
        ], [
            'foto_perfil.image'      => 'El archivo debe ser una imagen.',
            'foto_perfil.mimes'      => 'La foto debe ser JPG, PNG o WebP.',
            'foto_perfil.max'        => 'La foto no debe pesar más de 8 MB.',
            'foto_perfil.dimensions' => 'La foto debe medir al menos 200x200 píxeles.',
            'foto_perfil.uploaded'   => 'La foto no se pudo subir: excede el límite del servidor.',
        ]);

        $usuario = $request->user();

        if ($request->hasFile('foto_perfil')) {
            try {
                $usuario->foto_perfil = FotoPerfil::guardar($request->file('foto_perfil'), $usuario->foto_perfil);
            } catch (RuntimeException $e) {
                return back()->withErrors(['foto_perfil' => $e->getMessage()]);
            }

            $aviso = 'Tu foto de perfil se actualizó.';
        } elseif ($request->boolean('quitar_foto')) {
            FotoPerfil::borrar($usuario->foto_perfil);
            $usuario->foto_perfil = null;
            $aviso = 'Se quitó tu foto de perfil.';
        } else {
            return back()->withErrors(['foto_perfil' => 'Elige una imagen o marca "Quitar la foto actual".']);
        }

        $usuario->save();

        return back()->with('success', $aviso);
    }

    public function actualizarContrasena(Request $request)
    {
        $minimo = self::MINIMO_CONTRASENA;

        $request->validate([
            // 'confirmed' busca el campo password_confirmation y compara solo;
            // el mensaje de abajo es el que ve el usuario si no coinciden.
            'password' => "required|string|min:{$minimo}|confirmed",
        ], [
            'password.required'  => 'Escribe tu nueva contraseña.',
            'password.min'       => "La contraseña debe tener al menos {$minimo} caracteres.",
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
        ]);

        $usuario = $request->user();
        $usuario->password = Hash::make($request->input('password'));
        $usuario->save();

        return back()->with('success', 'Tu contraseña se actualizó.');
    }
}
