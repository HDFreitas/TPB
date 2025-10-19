<?php

namespace App\Modules\Plataforma\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // A autorização é feita pelo middleware CheckAdminRole
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    // Garante que o usuário pertence ao mesmo tenant
                    $query->where('tenant_id', auth()->user()->tenant_id);
                })
            ],
            'roles' => 'required|array|min:1',
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'O ID do usuário é obrigatório.',
            'user_id.integer' => 'O ID do usuário deve ser um número inteiro.',
            'user_id.exists' => 'Usuário não encontrado ou não pertence ao seu tenant.',
            'roles.required' => 'Pelo menos uma role deve ser informada.',
            'roles.array' => 'As roles devem ser enviadas como um array.',
            'roles.min' => 'Pelo menos uma role deve ser informada.',
            'roles.*.string' => 'Cada role deve ser uma string.',
            'roles.*.exists' => 'Uma ou mais roles informadas não existem.',
        ];
    }
}
