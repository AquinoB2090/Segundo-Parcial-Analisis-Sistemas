<?php

namespace App\Http\Requests;

class ListarCitasRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'integer', 'exists:doctores,id'],
            'paciente_id' => ['sometimes', 'integer', 'exists:pacientes,id'],
            'desde' => ['sometimes', 'date_format:Y-m-d'],
            'hasta' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }
}
