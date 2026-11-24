<?php

namespace App\Http\Controllers;

use App\Services\PesebreService;
use Illuminate\Http\JsonResponse;

class PesebreController extends Controller
{
    public function resumen(PesebreService $servicio): JsonResponse
    {
        return response()->json($servicio->resumen());
    }
}
