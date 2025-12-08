<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComprasRequest extends FormRequest
{
    public function authorize()
    {
        
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => ['required', 'exists:usuario,id'],
            'cafe_qtd' => ['integer', 'max:255'],
            'filtro_qtd' => ['integer', 'max:255'],
            'data_compra' => ['nullable', 'date'],
            'fila_id' => ['nullable', 'exists:fila,id'],
            
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.required' => 'O ID do usuário é obrigatório.',
            'usuario_id.exists' => 'O usuário informado não existe.',
            'cafe_qtd.required' => 'Informe a quantidade de café.',
            'cafe_qtd.integer' => 'A quantidade de Café deve ser um número inteiro.',
            'cafe_qtd.max' => 'A quantidade de Café deve ser menor ou igual a 255.',
            'filtro_qtd.integer' => 'A quantidade de filtros deve ser um número inteiro.',
            'filtro_qtd.max' => 'A quantidade de filtros deve ser menor ou igual a 255.'
        ];
    }
}
