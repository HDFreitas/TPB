<?php

namespace App\Modules\Plataforma\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Implementar autorização baseada em perfis
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'usuario' => 'required|string|max:255|unique:users,usuario',
            'dominio' => 'nullable|string|max:255', // Preenchido automaticamente
            'status' => 'boolean',
            // tenant_id não é aceito - sempre definido pelo usuário logado
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'usuario.required' => 'O usuário é obrigatório.',
            'usuario.unique' => 'Este usuário já está cadastrado.',
            'dominio.required' => 'O domínio é obrigatório.',
        ];
    }
}
