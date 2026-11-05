<?php
namespace App\Models\Admin; // padre
use Core\Model; // conexion a la base de datos
use PDO;

class Usuarios extends Model {
    protected $table = 'usuarios';

    public function registrar($data){
        // Logica para registrar un usuario
        $sql = "INSERT INTO {$this->table} (rol_id, nombre, apellidos, email, password_hash, telefono, activo, reset_password, token_password) VALUES (:rol_id, :nombre, :apellidos, :email, :password_hash, :telefono, :activo, :reset_password, :token_password)";
        
        $rolId = $data['rol'] ?? $data['rol_id'] ?? 2;
        $estado = isset($data['estado']) ? (int)$data['estado'] : (isset($data['activo']) ? (int)$data['activo'] : 1);
        $telefono = !empty($data['telefono']) ? trim($data['telefono']) : null;
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);

        $params = [ // array asociativo
            ':rol_id'         => (int)$rolId,
            ':nombre'         => trim($data['nombre']),
            ':apellidos'      => trim($data['apellidos']),
            ':email'          => trim(strtolower($data['email'])),
            ':password_hash'  => $hash,
            ':telefono'       => $telefono,
            ':activo'         => $estado,
            ':reset_password' => 0,
            ':token_password' => ''
        ];

        $preparado = self::$db->prepare($sql); // prepara la consulta
        return $preparado->execute($params); // ejecuta la consulta
    }

    public function buscar($criterio = null){
        // Logica para ver lista de usuarios o filtrar por busqueda
        if (!empty($criterio)) {
            $sql = "SELECT u.*, r.nombre AS rol_nombre 
                    FROM {$this->table} u 
                    INNER JOIN roles r ON u.rol_id = r.id 
                    WHERE u.nombre LIKE :criterio 
                       OR u.apellidos LIKE :criterio 
                       OR u.email LIKE :criterio 
                       OR u.telefono LIKE :criterio 
                       OR r.nombre LIKE :criterio 
                    ORDER BY u.id ASC";
            $preparado = self::$db->prepare($sql);
            $preparado->execute([':criterio' => '%' . trim($criterio) . '%']);
        } else {
            $sql = "SELECT u.*, r.nombre AS rol_nombre 
                    FROM {$this->table} u 
                    INNER JOIN roles r ON u.rol_id = r.id 
                    ORDER BY u.id ASC";
            $preparado = self::$db->prepare($sql);
            $preparado->execute();
        }
        return $preparado->fetchAll(PDO::FETCH_ASSOC); // reordena los resultados fetchAll
    }

    public function ver(Int $id){
        // Consultar un usuario por id
        $params = [':id' => $id];
        $sql = "SELECT u.*, r.nombre AS rol_nombre 
                FROM {$this->table} u 
                INNER JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = :id";
        $preparado = self::$db->prepare($sql);
        $preparado->execute($params);
        return $preparado->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($data){
        $rolId = $data['rol'] ?? $data['rol_id'] ?? 2;
        $estado = isset($data['estado']) ? (int)$data['estado'] : (isset($data['activo']) ? (int)$data['activo'] : 1);
        $telefono = !empty($data['telefono']) ? trim($data['telefono']) : null;

        $params = [
            ':rol_id'    => (int)$rolId,
            ':nombre'    => trim($data['nombre']),
            ':apellidos' => trim($data['apellidos']),
            ':email'     => trim(strtolower($data['email'])),
            ':telefono'  => $telefono,
            ':activo'    => $estado,
            ':id'        => (int)$data['id']
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

        $preparado = self::$db->prepare($sql);
        return $preparado->execute($params);
    }

    public function eliminar(Int $id){
        try {
            self::$db->beginTransaction();

            // Eliminar sesiones activas asociadas al usuario
            $stmtSes = self::$db->prepare("DELETE FROM sesiones WHERE usuario_id = :id");
            $stmtSes->execute([':id' => $id]);

            // Eliminar direcciones asociadas al usuario si existen
            $stmtDir = self::$db->prepare("DELETE FROM direcciones_usuario WHERE usuario_id = :id");
            $stmtDir->execute([':id' => $id]);

            // Eliminar usuario
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $preparado = self::$db->prepare($sql);
            $res = $preparado->execute([':id' => $id]);

            self::$db->commit();
            return $res;
        } catch (\PDOException $e) {
            if (self::$db->inTransaction()) {
                self::$db->rollBack();
            }
            return false;
        }
    }

    public function ver_ventas_usuario(Int $id){
        // Verifica si el usuario tiene ventas asociadas
        $sql = "SELECT id FROM ventas WHERE usuario_id = :id LIMIT 1";
        $params = [':id' => $id];
        $preparado = self::$db->prepare($sql);
        $preparado->execute($params);
        return $preparado->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorEmail(string $email, ?int $excluirId = null){
        // Validacion de correo duplicado
        if ($excluirId !== null) {
            $sql = "SELECT id FROM {$this->table} WHERE email = :email AND id != :id LIMIT 1";
            $preparado = self::$db->prepare($sql);
            $preparado->execute([':email' => trim(strtolower($email)), ':id' => (int)$excluirId]);
        } else {
            $sql = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";
            $preparado = self::$db->prepare($sql);
            $preparado->execute([':email' => trim(strtolower($email))]);
        }
        return $preparado->fetch(PDO::FETCH_ASSOC);
    }
}

