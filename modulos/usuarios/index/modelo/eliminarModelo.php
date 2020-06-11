<?php
/* Clase eliminarModelo */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo;

final class eliminarModelo extends Modelo
{
  
    public function __construct() {
        parent::__construct();
    }
  
    public function buscarUsuarioId(int $id) {
        $sql = "SELECT u.id, p.alias, u.email, p.nombre, p.apellido, p.rut, p.telefono, p.celular, p.direccion, p.region, p.provincia, p.comuna, r.role AS cargo, rg.nombre AS region, pr.nombre AS provincia, c.nombre AS comuna
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id 
        LEFT JOIN roles r ON r.id = u.role 
        LEFT JOIN region rg ON rg.id = p.region 
        LEFT JOIN provincia pr ON pr.id = p.provincia 
        LEFT JOIN comuna c ON c.id = p.comuna 
        WHERE u.deleted_at IS NULL AND u.id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function eliminarUsuario( int $id, string $codigo, string $deleted_at, int $user_id ) {
        $sql = "UPDATE usuarios SET activado = 0, codigo = :codigo, updated_at = :updated_at, deleted_at = :deleted_at, user_id = :user_id WHERE id = :id";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':codigo', $codigo, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $deleted_at, \PDO::PARAM_STR);
        $stmt->bindParam(':deleted_at', $deleted_at, \PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    public function getRoles() {
        return $this->_db->query("SELECT * FROM roles")->fetchAll();
    }
}