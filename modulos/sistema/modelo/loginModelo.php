<?php
/* Clase loginModelo */
namespace Sistema\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class loginModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function validarLogin($dato) {
        $sql = "SELECT u.id, u.email, u.clave, DATE(u.created_at) AS creado, u.role, u.activado, p.alias, p.nombre, p.apellido, p.rut, p.telefono, p.celular, p.direccion, p.region, p.provincia, p.comuna, p.resumen, p.imagen 
		FROM usuarios u 
		INNER JOIN perfiles p ON p.user_id = u.id 
		WHERE p.alias = '{$dato}' OR u.email = '{$dato}';";
        return $this->_db->query($sql)->fetch();
    }

    public function obtenerCargo($idRole) {
        $sql = "SELECT role FROM roles WHERE id = :idRole;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':idRole', $idRole, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function enLinea(int $id)
    {
        $sql = "UPDATE usuarios SET enlinea = 1 WHERE activado = 1 AND id = :id";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
  
}