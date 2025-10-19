<?php

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseFormRequest;

/**
 * @OA\Schema(
 *     schema="LoginUserRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="admin@sancon.com.br",
 *         description="Email do usuário"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="admin123",
 *         description="Senha do usuário"
 *     )
 * )
 */
class LoginUserRequest extends BaseFormRequest
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
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
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
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'email.max' => 'O campo email não pode ter mais de 255 caracteres.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'A senha deve ser uma string válida.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
        ];
    }
}