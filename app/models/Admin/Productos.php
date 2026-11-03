<?php
namespace App\Models\Admin;//padre
use Core\Model; //conexion a la base de datos
use PDO;
class Productos extends Model {
    protected $table = 'productos';

    public function registrar($data){
        // Lógica para registrar un producto
        $sql = "INSERT INTO {$this->table} (categoria_id, codigo_sku, nombre, descripcion, precio_venta, stock_actual) VALUES (:categoria_id, :codigo_sku, :nombre, :descripcion, :precio_venta, :stock_actual)"; //this representa a la clase donde se está ejecutando, variable de contexto
        $params = [ //array asociativo
            ':categoria_id' => $data['categoria'],
            ':codigo_sku' => $data['codigo'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':precio_venta' => $data['precio'],
            ':stock_actual' => $data['stock']
        ];
        $preparado = self::$db->prepare($sql); //prepara la consulta
        return $preparado->execute($params); //ejecuta la consulta
    }
    public function buscar(){
        // Lógica para ver lista de productos
        $sql = "SELECT p.*, c.nombre AS categoria_nombre FROM {$this->table} p INNER JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id ASC";
        $preparado = self::$db->prepare($sql);//prepara la consulta
        $preparado->execute();//ejecuta la consulta
        return $preparado->fetchAll(PDO::FETCH_ASSOC);//reordena los resultados fetchAll cuando son varios 1 o más
    }
    public function ver(Int $id){
        $params = [':id' => $id];
        $sql = "SELECT * FROM {$this->table} WHERE id=:id";
        $preparado = self::$db->prepare($sql);
        $preparado->execute($params);
        return $preparado->fetch(PDO::FETCH_ASSOC);
    }
    public function actualizar($data){
        $params = [
            ':categoria_id' => $data['categoria'],
            ':codigo_sku' => $data['codigo'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':precio_venta' => $data['precio'],
            ':stock_actual' => $data['stock'],
            ':activo' => $data['estado'],
            ':id' => $data['id']
        ];
        $sql="UPDATE {$this->table} SET categoria_id=:categoria_id, codigo_sku=:codigo_sku, nombre=:nombre, descripcion=:descripcion, precio_venta=:precio_venta, stock_actual=:stock_actual, activo=:activo WHERE id=:id";
        $preparado = self::$db->prepare($sql);
        return $preparado->execute($params);
    }
    public function eliminar($id){
       $sql = "DELETE FROM {$this->table} WHERE id=:id";
       $params = [':id' => $id];
       $preparado = self::$db->prepare($sql);
       return $preparado->execute($params);
    }
}