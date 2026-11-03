<?php

namespace App\Models\Admin;

use Core\Model;
use PDO;

class Usuarios extends Model
{
    protected $table = 'usuarios';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene la lista completa de usuarios con su respectivo rol
     */
    public function listar(): array
    {
        $sql = "SELECT u.*, r.nombre AS rol_nombre 
                FROM {$this->table} u 
                INNER JOIN roles r ON u.rol_id = r.id 
                ORDER BY u.id DESC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Alias de listar para mantener compatibilidad con convenciones de otros modelos
     */
    public function buscar(): array
    {
        return $this->listar();
    }

    /**
     * Consulta un usuario por su ID con datos complementarios (rol, totales relacionados)
     */
    public function ver(int $id): ?array
    {
        $sql = "SELECT u.*, r.nombre AS rol_nombre, r.descripcion AS rol_descripcion,
                       (SELECT COUNT(*) FROM ventas v WHERE v.usuario_id = u.id) AS total_ventas,
                       (SELECT COUNT(*) FROM direcciones_usuario d WHERE d.usuario_id = u.id) AS total_direcciones,
                       (SELECT COUNT(*) FROM sesiones s WHERE s.usuario_id = u.id AND s.activa = 1) AS sesiones_activas
                FROM {$this->table} u 
                INNER JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Busca un usuario por correo electrónico (útil para validación de unicidad)
     */
    public function obtenerPorEmail(string $email, ?int $excluirId = null): ?array
    {
        if ($excluirId !== null) {
            $sql = "SELECT id, email FROM {$this->table} WHERE email = :email AND id != :id LIMIT 1";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([':email' => $email, ':id' => $excluirId]);
        } else {
            $sql = "SELECT id, email FROM {$this->table} WHERE email = :email LIMIT 1";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([':email' => $email]);
        }
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Registra un nuevo usuario en la base de datos
     */
    public function registrar(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} 
                (rol_id, nombre, apellidos, email, password_hash, telefono, activo, reset_password, token_password) 
                VALUES 
                (:rol_id, :nombre, :apellidos, :email, :password_hash, :telefono, :activo, :reset_password, :token_password)";

        $params = [
            ':rol_id'         => (int)$data['rol_id'],
            ':nombre'         => trim($data['nombre']),
            ':apellidos'      => trim($data['apellidos']),
            ':email'          => trim(strtolower($data['email'])),
            ':password_hash'  => password_hash($data['password'], PASSWORD_BCRYPT),
            ':telefono'       => !empty($data['telefono']) ? trim($data['telefono']) : null,
            ':activo'         => isset($data['activo']) ? (int)$data['activo'] : 1,
            ':reset_password' => 0,
            ':token_password' => ''
        ];

        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Actualiza la información de un usuario existente
     */
    public function actualizar(array $data): bool
    {
        $params = [
            ':id'        => (int)$data['id'],
            ':rol_id'    => (int)$data['rol_id'],
            ':nombre'    => trim($data['nombre']),
            ':apellidos' => trim($data['apellidos']),
            ':email'     => trim(strtolower($data['email'])),
            ':telefono'  => !empty($data['telefono']) ? trim($data['telefono']) : null,
            ':activo'    => isset($data['activo']) ? (int)$data['activo'] : 1
        ];

        if (!empty($data['password'])) {
            $sql = "UPDATE {$this->table} 
                    SET rol_id = :rol_id, 
                        nombre = :nombre, 
                        apellidos = :apellidos, 
                        email = :email, 
                        password_hash = :password_hash, 
                        telefono = :telefono, 
                        activo = :activo 
                    WHERE id = :id";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            $sql = "UPDATE {$this->table} 
                    SET rol_id = :rol_id, 
                        nombre = :nombre, 
                        apellidos = :apellidos, 
                        email = :email, 
                        telefono = :telefono, 
                        activo = :activo 
                    WHERE id = :id";
        }

        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Verifica si el usuario tiene ventas asociadas
     */
    public function tieneVentas(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM ventas WHERE usuario_id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Elimina un usuario y limpia sus sesiones y direcciones asociadas
     */
    public function eliminar(int $id): bool
    {
        try {
            self::$db->beginTransaction();

            // 1. Eliminar sesiones del usuario
            $stmtSes = self::$db->prepare("DELETE FROM sesiones WHERE usuario_id = :id");
            $stmtSes->execute([':id' => $id]);

            // 2. Eliminar direcciones del usuario
            $stmtDir = self::$db->prepare("DELETE FROM direcciones_usuario WHERE usuario_id = :id");
            $stmtDir->execute([':id' => $id]);

            // 3. Eliminar el usuario
            $stmt = self::$db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $res = $stmt->execute([':id' => $id]);

            self::$db->commit();
            return $res;
        } catch (\PDOException $e) {
            if (self::$db->inTransaction()) {
                self::$db->rollBack();
            }
            error_log("Error al eliminar usuario ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado activo/inactivo de un usuario
     */
    public function cambiarEstado(int $id, int $nuevoEstado): bool
    {
        $sql = "UPDATE {$this->table} SET activo = :activo WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([':activo' => $nuevoEstado, ':id' => $id]);
    }

    /**
     * Obtiene la lista de roles disponibles
     */
    public function obtenerRoles(): array
    {
        $sql = "SELECT id, nombre, descripcion FROM roles ORDER BY id ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

