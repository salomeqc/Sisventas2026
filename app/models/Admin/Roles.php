<?php
namespace App\Models\Admin;

use Core\Model;
use PDO;

class Roles extends Model {
    protected $table = 'roles';

    public function roles_select(){
        // Obtener todos los roles disponibles
        $sql = "SELECT id, nombre, descripcion FROM {$this->table} ORDER BY id ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(){
        // Listar roles
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ver(Int $id){
        // Consultar rol por ID
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

