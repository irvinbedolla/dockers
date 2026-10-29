<?php

namespace App\Http\Requests\Sinotifico;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:60'],
            'password' => ['required', 'string', 'max:191'],
            // Nombre del teléfono o navegador; aparece en la lista de tokens.
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Escribe tu usuario para continuar.',
            'email.email' => 'El usuario se escribe como un correo electrónico.',
            'password.required' => 'Escribe tu contraseña para continuar.',
        ];
    }
}
