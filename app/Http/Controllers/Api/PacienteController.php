<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PacienteResource;
use App\Models\Paciente;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PacienteController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PacienteResource::collection(Paciente::query()->orderBy('nombre')->get());
    }
}
