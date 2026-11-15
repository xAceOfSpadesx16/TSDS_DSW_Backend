<?php

namespace App\Modules\History\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validación del alta de historial. */
class CreateRequest extends FormRequest
{
    /** La ruta ya exige `auth:api`. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas del alta de historial.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'module_name' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'max:255'],
            'payload' => ['required', 'array'],
            'result' => ['required', 'array'],
        ];
    }

    /** Descarta `user_id` del cuerpo para evitar suplantación. */
    protected function prepareForValidation(): void
    {
        $this->request->remove('user_id');
    }
}