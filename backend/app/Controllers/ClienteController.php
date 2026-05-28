<?php
declare(strict_types=1);

namespace GemMotors\Controllers;

use GemMotors\Middleware\AuthMiddleware;
use GemMotors\Middleware\RolMiddleware;
use GemMotors\Models\Cliente;
use GemMotors\Config\App;

class ClienteController
{
    public static function index(): void
    {
        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $clientes = Cliente::findAll();
        App::jsonResponse(true, $clientes, 'Clientes obtenidos');
    }

    public static function show(int $id): void
    {
        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $cliente = Cliente::find($id);
        if (!$cliente) {
            App::jsonResponse(false, null, 'Cliente no encontrado', 404);
            return;
        }
        App::jsonResponse(true, $cliente, 'Cliente obtenido');
    }

    public static function create(): void
    {
        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['nombre']) || empty($input['dni_ruc'])) {
            App::jsonResponse(false, null, 'Nombre y DNI/RUC son requeridos', 400);
            return;
        }

        try {
            $cliente = Cliente::create($input);
            App::jsonResponse(true, $cliente, 'Cliente creado exitosamente', 201);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '23505')) {
                App::jsonResponse(false, null, 'El DNI/RUC ya está registrado', 409);
                return;
            }
            App::jsonResponse(false, null, 'Error al crear cliente: ' . $e->getMessage(), 500);
        }
    }

    public static function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $cliente = Cliente::find($id);
        if (!$cliente) {
            App::jsonResponse(false, null, 'Cliente no encontrado', 404);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $cliente->update($input);
            App::jsonResponse(true, $cliente, 'Cliente actualizado correctamente');
        } catch (\Exception $e) {
            App::jsonResponse(false, null, 'Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public static function getHistorial(int $id): void
    {
        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $cliente = Cliente::find($id);
        if (!$cliente) {
            App::jsonResponse(false, null, 'Cliente no encontrado', 404);
            return;
        }

        $historial = $cliente->getHistorial();
        $data = array_map(function($row) {
            $row['vehiculo'] = ['marca' => $row['marca'], 'modelo' => $row['modelo'], 'placa' => $row['placa']];
            return $row;
        }, $historial);

        App::jsonResponse(true, $data, 'Historial obtenido');
    }
}