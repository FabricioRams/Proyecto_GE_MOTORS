<?php
declare(strict_types=1);

namespace GemMotors\Controllers;

use GemMotors\Middleware\AuthMiddleware;
use GemMotors\Middleware\RolMiddleware;
use GemMotors\Models\Usuario;
use GemMotors\Config\App;

class UsuarioController
{
    public static function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            App::jsonResponse(false, null, 'Método no permitido', 405);
            return;
        }

        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $usuarios = Usuario::findAll();

        $usuariosData = array_map(function ($usuario) {
            return [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'activo' => $usuario->activo
            ];
        }, $usuarios);

        App::jsonResponse(true, $usuariosData, 'Usuarios obtenidos');
    }

    /**
     * Crear un nuevo usuario en el sistema
     * POST /api/usuarios
     */
    public static function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::jsonResponse(false, null, 'Método no permitido', 405);
            return;
        }

        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            App::jsonResponse(false, null, 'JSON inválido', 400);
            return;
        }

        $nombre = $input['nombre'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $rol = $input['rol'] ?? 'mecanico';
        $activo = isset($input['activo']) ? (bool)$input['activo'] : true;

        if (empty($nombre) || empty($email) || empty($password)) {
            App::jsonResponse(false, null, 'Nombre, email y contraseña son requeridos', 400);
            return;
        }

        try {
            // Cifrar la contraseña antes de guardarla (Seguridad)
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $usuario = Usuario::create([
                'nombre' => $nombre,
                'apellido' => '', // El formulario actual maneja nombre completo
                'email' => $email,
                'password_hash' => $hashedPassword,
                'rol' => $rol,
                'activo' => $activo
            ]);

            App::jsonResponse(true, ['id' => $usuario->id, 'nombre' => $usuario->nombre, 'email' => $usuario->email, 'rol' => $usuario->rol, 'activo' => $usuario->activo], 'Usuario creado exitosamente', 201);
        } catch (\Exception $e) {
            App::jsonResponse(false, null, 'Error al crear usuario: ' . $e->getMessage(), 500);
        }
    }

    public static function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            App::jsonResponse(false, null, 'Método no permitido', 405);
            return;
        }

        AuthMiddleware::requireAuth();
        RolMiddleware::checkRole();

        $usuario = Usuario::find($id);
        if ($usuario === null) {
            App::jsonResponse(false, null, 'Usuario no encontrado', 404);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            App::jsonResponse(false, null, 'JSON inválido', 400);
            return;
        }

        $rol = $input['rol'] ?? $usuario->rol;
        $activo = $input['activo'] ?? $usuario->activo;

        $usuario->update([
            'rol' => $rol,
            'activo' => $activo
        ]);

        App::jsonResponse(true, [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'email' => $usuario->email,
            'rol' => $usuario->rol,
            'activo' => $usuario->activo
        ], 'Usuario actualizado');
    }
}