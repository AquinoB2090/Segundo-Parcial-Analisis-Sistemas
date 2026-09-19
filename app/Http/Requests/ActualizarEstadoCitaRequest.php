<?php

namespace App\Http\Requests;

use App\Enums\EstadoCita;
use Illuminate\Validation\Rule;

class ActualizarEstadoCitaRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::enum(EstadoCita::class)],
        ];
    }
}
