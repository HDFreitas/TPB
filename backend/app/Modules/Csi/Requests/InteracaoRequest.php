<?php

namespace App\Modules\Csi\Requests;

use App\Http\Requests\V1\BaseFormRequest;

/**
 * @OA\Schema(
 *     schema="InteracaoRequest",
 *     type="object",
 *     required={"cliente_id", "tipo", "data_interacao", "titulo", "descricao"},
 *     @OA\Property(
 *         property="cliente_id",
 *         type="integer",
 *         example=1,
 *         description="ID do cliente"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=1,
 *         description="ID do usuário"
 *     ),
 *     @OA\Property(
 *         property="tipo",
 *         type="string",
 *         description="Tipo da interação (enum temporário)"
 *     ),
 *     @OA\Property(
 *         property="origem",
 *         type="string",
 *         example="Ouvidoria Senior",
 *         description="Origem da interação"
 *     ),
 *     @OA\Property(
 *         property="titulo",
 *         type="string",
 *         example="Acionamento de Gestão - SLA crítico",
 *         description="Título da interação"
 *     ),
 *     @OA\Property(
 *         property="descricao",
 *         type="string",
 *         example="Cliente reportou incidentes recorrentes. Necessário acompanhamento da gestão.",
 *         description="Descrição detalhada da interação"
 *     ),
 *     @OA\Property(
 *         property="chave",
 *         type="string",
 *         example="TCK-12345",
 *         description="Chave/identificador na origem"
 *     ),
 *     @OA\Property(
 *         property="valor",
 *         type="number",
 *         format="decimal",
 *         example=1500.00,
 *         description="Valor monetário relacionado (quando aplicável)"
 *     ),
 *     @OA\Property(
 *         property="data_interacao",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-15 14:30:00",
 *         description="Data da interação"
 *     )
 * )
 */
class InteracaoRequest extends BaseFormRequest
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
        $rules = [
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_interacao_id' => 'required|exists:tipos_interacao,id',
            'origem' => 'required|string',
            'data_interacao' => 'required|date|before_or_equal:now',
            'titulo' => 'required|string',
            'descricao' => 'required|string|min:10',
            'chave' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
        ];

        // Para updates, tornar campos opcionais
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['cliente_id'] = 'sometimes|exists:clientes,id';
            $rules['tipo_interacao_id'] = 'sometimes|exists:tipos_interacao,id';
            $rules['origem'] = 'sometimes|string';
            $rules['data_interacao'] = 'sometimes|date|before_or_equal:now';
            $rules['titulo'] = 'sometimes|string';
            $rules['descricao'] = 'sometimes|string|min:10';
            $rules['chave'] = 'sometimes|nullable|string';
            $rules['valor'] = 'sometimes|nullable|numeric|min:0';
            $rules['user_id'] = 'sometimes|nullable|exists:users,id';
        }

        return $rules;
    }

    /**
     * Get the custom messages for the validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'cliente_id.required' => 'O campo cliente é obrigatório.',
            'cliente_id.exists' => 'O cliente selecionado não existe.',
            'tipo.required' => 'O campo tipo é obrigatório.',
            'tipo.in' => 'Tipo inválido.',
            'origem.required' => 'O campo origem é obrigatório.',
            'data_interacao.required' => 'O campo data da interação é obrigatório.',
            'data_interacao.date' => 'A data da interação deve ser uma data válida.',
            'data_interacao.before_or_equal' => 'A data da interação não pode ser futura.',
            'titulo.required' => 'O campo título é obrigatório.',
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.min' => 'A descrição deve ter pelo menos :min caracteres.',
            'valor.numeric' => 'O campo valor deve ser numérico.',
            'valor.min' => 'O campo valor deve ser maior ou igual a zero.',
            'user_id.exists' => 'O usuário selecionado não existe.',
        ];
    }
}
