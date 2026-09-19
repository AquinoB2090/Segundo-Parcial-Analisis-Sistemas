<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class GuardarCitaRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'doctor_id' => [
                'required',
                'integer',
                Rule::exists('doctores', 'id')->where('activo', true),
            ],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'motivo' => ['required', 'string', 'max:1000'],
            'estado' => ['sometimes', Rule::in(['pendiente', 'confirmada', 'cancelada', 'atendida'])],
        ];
    }
}
