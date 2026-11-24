<?php

namespace Database\Seeders;

use App\Services\AnimalService;
use App\Services\RecursoService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Datos de ejemplo creados a través de los servicios (respetan las reglas). */
    public function run(RecursoService $recursos, AnimalService $animales): void
    {
        $recursos->crear(['nombre' => 'Paja del establo', 'categoria' => 'PAJA', 'cantidad' => 40, 'stockMinimo' => 10]);
        $recursos->crear(['nombre' => 'Mantas de lana', 'categoria' => 'MANTA', 'cantidad' => 3, 'stockMinimo' => 5]);
        $recursos->crear(['nombre' => 'Agua del pozo', 'categoria' => 'AGUA', 'cantidad' => 25, 'stockMinimo' => 10]);
        $recursos->crear(['nombre' => 'Heno y cebada', 'categoria' => 'ALIMENTO_ANIMALES', 'cantidad' => 30, 'stockMinimo' => 8]);
        $recursos->crear(['nombre' => 'Lámparas de aceite', 'categoria' => 'LAMPARA_ACEITE', 'cantidad' => 0, 'stockMinimo' => 2]);

        $animales->crear(['nombre' => 'Buey Manso', 'especie' => 'BUEY', 'racionDiaria' => 6]);
        $animales->crear(['nombre' => 'Mula Paciente', 'especie' => 'MULA', 'racionDiaria' => 4]);
        $animales->crear(['nombre' => 'Ovejita Blanca', 'especie' => 'OVEJA', 'racionDiaria' => 2]);
    }
}
