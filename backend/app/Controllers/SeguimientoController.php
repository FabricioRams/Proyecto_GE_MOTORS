<?php
declare(strict_types=1);

namespace GemMotors\Controllers;

use GemMotors\Config\App;
use GemMotors\Models\OrdenTrabajo;
use GemMotors\Models\Evidencia;

class SeguimientoController
{
    public static function track(string $codigo): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            App::jsonResponse(false, null, 'Método no permitido', 405);
            return;
        }

        $orden = OrdenTrabajo::findByNumeroOt($codigo);
        if ($orden === null) {
            App::jsonResponse(false, null, 'Orden de trabajo no encontrada', 404);
            return;
        }

        $evidencias = $orden->getEvidencias();
        $vehiculo = $orden->getVehiculo();

        $evidenciasData = array_map(function ($ev) {
            return [
                'id' => $ev->id,
                'tipo' => $ev->tipo === 'foto' ? 'imagen' : $ev->tipo,
                'url' => $ev->url_cloudinary,
                'etiqueta' => $ev->etiqueta,
                'descripcion' => $ev->descripcion
            ];
        }, $evidencias);

        App::jsonResponse(true, [
            'orden' => [
                'id' => $orden->id,
                'numero_ot' => $orden->numero_ot,
                'estado' => $orden->estado,
                'created_at' => $orden->created_at,
                'vehiculo' => $vehiculo ? [
                    'marca' => $vehiculo->marca,
                    'modelo' => $vehiculo->modelo,
                    'placa' => $vehiculo->placa
                ] : null
            ],
            'evidencias' => $evidenciasData
        ], 'Orden encontrada');
    }
}