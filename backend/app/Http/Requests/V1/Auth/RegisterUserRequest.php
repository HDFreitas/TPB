<?php

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseFormRequest;

/**
 * @OA\Schema(
 *     schema="RegisterUserRequest",
 *     type="object",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="João Silva",
 *         description="Nome completo do usuário"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="joao@exemplo.com",
 *         description="Email do usuário"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="senha123",
 *         description="Senha do usuário (mínimo 8 caracteres)"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="senha123",
 *         description="Confirmação da senha"
 *     )
 * )
 */
class RegisterUserRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'usuario' => 'required|string|max:255|unique:users',
            'dominio' => 'required|nullable|string|max:255',
        ];
    }

    /**
     * Get the custom messages for the validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'email.unique' => 'O email informado já está em uso.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.string' => 'A senha deve ser uma string válida.',
            'name.string' => 'O nome deve ser uma string válida.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
        ];
    }
}