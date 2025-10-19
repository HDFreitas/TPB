<?php

namespace App\Modules\Plataforma\Requests;

use App\Http\Requests\V1\BaseFormRequest;

/**
 * @OA\Schema(
 *     schema="LoginUserRequest",
 *     type="object",
 *     required={"login", "password"},
 *     @OA\Property(
 *         property="login",
 *         type="string",
 *         example="admin@sancon.com.br",
 *         description="Login do usuário no formato usuario@dominio.com.br"
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
            'login' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z.]+@[a-z]+\.com\.br$/'
            ],
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
            'login.required' => 'O campo login é obrigatório.',
            'login.string' => 'O login deve ser uma string válida.',
            'login.max' => 'O login não pode ter mais de 255 caracteres.',
            'login.regex' => 'O login deve estar no formato: usuario@dominio.com.br',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'A senha deve ser uma string válida.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
        ];
    }
}