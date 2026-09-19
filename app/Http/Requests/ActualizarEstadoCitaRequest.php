<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ActualizarEstadoCitaRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['pendiente', 'confirmada', 'cancelada', 'atendida'])],
        ];
    }
}
