<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInteracaoRequest extends FormRequest
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
            'tenant_id' => 'required|exists:tenants,id',
            'cliente_id' => 'required|exists:clientes,id',
            'tipo' => 'required|string|max:255',
            'origem' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'data_interacao' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tenant_id.required' => 'O tenant é obrigatório.',
            'tenant_id.exists' => 'O tenant selecionado não existe.',
            'cliente_id.required' => 'O cliente é obrigatório.',
            'cliente_id.exists' => 'O cliente selecionado não existe.',
            'tipo.required' => 'O tipo da interação é obrigatório.',
            'origem.required' => 'A origem da interação é obrigatória.',
            'data_interacao.required' => 'A data da interação é obrigatória.',
            'data_interacao.date' => 'A data da interação deve ser uma data válida.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
        ];
    }
}
