<?php
/* Clase ajaxModelo.php */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo;

final class ajaxModelo extends Modelo
{
  
    public function __construct() {
        parent::__construct();
    }
    
    public function usuarioId(int $id) {
        $sql = "SELECT u.id, p.alias, CONCAT(IFNULL(p.nombre, '--'), ' ', IFNULL(p.apellido, '--')) as nombres, u.email, IFNULL(p.rut, '--') as rut, IFNULL(p.telefono, '--') as telefono, p.imagen, p.resumen, DATE(u.created_at) AS creado, r.role AS cargo 
            FROM usuarios u 
            LEFT JOIN perfiles p ON u.id = p.user_id 
            LEFT JOIN roles r ON u.role = r.id 
            WHERE u.id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
  
}