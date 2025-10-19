<?php

namespace App\Http\Requests\V1;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

abstract class BaseFormRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Sobrescreve o método padrão para manipular uma tentativa de validação com falha.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Cria a resposta de erro usando nosso Trait
        $response = $this->errorResponse(
            'Os dados fornecidos são inválidos.',
            Response::HTTP_UNPROCESSABLE_ENTITY, // Código 422
            $validator->errors()->toArray()
        );

        // Lança uma exceção com a nossa resposta JSON customizada
        throw new HttpResponseException($response);
    }
}