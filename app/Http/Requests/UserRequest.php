<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [
            "name.required" => "O campo nome é obrigatório",
            "email.required" => "O campo e-mail é obrigatório",
            "email.email" => "O campo e-mail deve ser um e-mail válido",
            "email.max" => "O campo e-mail deve ter no máximo 255 caracteres",
            "email.unique" => "O e-mail informado já está em uso",
            "password.required" => "O campo senha é obrigatório",
            "password.min" => "O campo senha deve ter no mínimo 8 caracteres",
            "password.max" => "O campo senha deve ter no máximo 255 caracteres",
            "confirm_password.required" => "O campo confirmar senha é obrigatório",
            "confirm_password.min" => "O campo confirmar senha deve ter no mínimo 8 caracteres",
            "confirm_password.max" => "O campo confirmar senha deve ter no máximo 255 caracteres",
            "email.exists" => "O e-mail informado não existe",
            "password.confirmed" => "As senhas não conferem"
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        switch ($this->route()->getActionMethod()) {
            case "login":
                return [
                    "email" => "required|email|max:255|exists:users,email",
                    "password" => "required|min:8|max:255"
                ];
                break;
            case "register":
                return [
                    "name" => "required",
                    "email" => "required|email|max:255",
                    "password" => "required|min:8|max:255",
                    "confirm_password" => "required|min:8|max:255"
                ];
            case "update_user":
                return [
                    "name" => "required",
                    "status" => "sometimes",
                    "file" => "sometimes",
                ];
            default:
                return [];
                break;
        }
    }
}
