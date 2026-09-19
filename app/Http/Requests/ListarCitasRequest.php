<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

class ListarCitasRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'integer', 'exists:doctores,id'],
            'paciente_id' => ['sometimes', 'integer', 'exists:pacientes,id'],
            'desde' => ['sometimes', 'date_format:Y-m-d'],
            'hasta' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $desde = $this->input('desde');
            $hasta = $this->input('hasta');

            if (! $validator->errors()->hasAny(['desde', 'hasta'])
                && is_string($desde)
                && is_string($hasta)
                && $hasta < $desde) {
                $validator->errors()->add('hasta', 'La fecha hasta debe ser igual o posterior a la fecha desde.');
            }
        }];
    }
}
