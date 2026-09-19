<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $fillable = ['nombre', 'email', 'telefono', 'fecha_nacimiento'];

    protected function casts(): array
    {
        return ['fecha_nacimiento' => 'date:Y-m-d'];
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
