<?php

namespace App\Exceptions;

use RuntimeException;

class HorarioNoDisponibleException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El doctor ya tiene una cita activa en el horario solicitado.');
    }
}
