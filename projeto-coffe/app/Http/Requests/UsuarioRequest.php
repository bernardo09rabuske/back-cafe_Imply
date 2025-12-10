<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
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
       
        $id = $this->route('id');

        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'regex:/@gmail\.com$/i',
                Rule::unique('usuario')->ignore($id),
            ],
            'senha' => [
                'nullable',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^\w\s])[^\s]{8,}$/'
            ],
            'acesso' => ['nullable', 'string', 'in:admin,usuario'],
        ];
    }
    public function messages()
    {
        return [
            'email.regex' => 'O e-mail deve terminar com "@gmail.com".',
            'email.unique' => 'Este e-mail já está em uso.',
            'senha.regex' => 'A senha deve conter pelo menos:
                - 1 letra maiúscula,
                - 1 letra minúscula,
                - 1 número,
                - 1 caractere especial,
                - e ter pelo menos 8 caracteres.',
        ];
    }
}
