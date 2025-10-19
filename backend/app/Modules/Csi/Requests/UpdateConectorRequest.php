<?php

namespace App\Modules\Csi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateConectorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('conectores.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tenant_id' => 'nullable|exists:tenants,id',
            'codigo' => 'sometimes|string|max:50|in:1-ERP,2-Movidesk,3-CRM Eleve',
            'nome' => 'sometimes|string|max:255',
            'url' => 'nullable|url|max:500',
            'status' => 'sometimes|boolean',
            'usuario' => 'nullable|string|max:255',
            'senha' => 'nullable|string|max:255',
            'token' => 'nullable|string',
            'configuracao_adicional' => 'nullable|array',
            'observacoes' => 'nullable|string'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tenant_id.exists' => 'O tenant selecionado não existe.',
            'codigo.in' => 'O código deve ser um dos tipos válidos: 1-ERP, 2-Movidesk, 3-CRM Eleve.',
            'url.url' => 'A URL deve ter um formato válido.',
            'usuario.required_if' => 'O usuário é obrigatório para conectores ERP.',
            'senha.required_if' => 'A senha é obrigatória para conectores ERP.',
            'token.required_if' => 'O token é obrigatório para conectores Movidesk e CRM Eleve.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            
            // Validações condicionais baseadas no tipo de conector
            if (isset($data['codigo'])) {
                switch ($data['codigo']) {
                    case '1-ERP':
                        if (empty($data['usuario'])) {
                            $validator->errors()->add('usuario', 'O usuário é obrigatório para conectores ERP.');
                        }
                        if (empty($data['senha'])) {
                            $validator->errors()->add('senha', 'A senha é obrigatória para conectores ERP.');
                        }
                        break;
                    case '2-Movidesk':
                    case '3-CRM Eleve':
                        if (empty($data['token'])) {
                            $validator->errors()->add('token', 'O token é obrigatório para este tipo de conector.');
                        }
                        break;
                }
            }
        });
    }
}
