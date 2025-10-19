<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
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
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'codigo_ramo' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'cnpj_cpf' => 'required|string|max:20|unique:clientes,cnpj_cpf',
            'codigo_senior' => 'nullable|string|max:255|unique:clientes,codigo_senior',
            'status' => 'boolean',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cep' => 'nullable|string|max:10',
            'observacoes' => 'nullable|string|max:1000'
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
            'razao_social.required' => 'A razão social do cliente é obrigatório.',
            'razao_social.max' => 'A razão social do cliente não pode ter mais de 255 caracteres.',
            'cnpj_cpf.required' => 'O CNPJ/CPF é obrigatório.',
            'cnpj_cpf.unique' => 'Este CNPJ/CPF já está cadastrado.',
            'codigo_senior.unique' => 'Este código Senior já está cadastrado.',
            'email.email' => 'O email deve ter um formato válido.',
            'estado.max' => 'A UF deve ter no máximo 2 caracteres.',
            'cep.max' => 'O CEP deve ter no máximo 10 caracteres.',
            'observacoes.max' => 'As observações não podem ter mais de 1000 caracteres.'
        ];
    }
}
