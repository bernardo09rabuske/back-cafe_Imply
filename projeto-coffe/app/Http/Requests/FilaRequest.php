<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class FilaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function messages()
    {
        return [
            'usuario_id.exists' => 'O usuário informado não existe.',
        ];
    }
    public function rules()
{
    return [
        'usuario_id' => ['required', 'integer', 'exists:usuario,id'],
        'ativo' => ['boolean'],
    ];
}
    
}

