<?php
/* Clase revisarModelo */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class revisarModelo extends Modelo {
  
  public function __construct() {
		parent::__construct();
	}

	public function buscarUsuario($id) {
		$sql = "SELECT u.id, u.email, u.role, u.activado, u.deleted_at, u.codigo, p.*, r.role as cargo 
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id 
        INNER JOIN roles r ON u.role = r.id 
        WHERE u.id = :id;";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();
		$usuario = $stmt->fetch();
		unset($usuario['clave']);
		return $usuario;
	}
  
	public function obtenerEmail($email) {
		$sql = "SELECT * FROM usuarios WHERE email = :email;";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':email', $email, \PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch();
	}
  
	public function cargarRoles($idRole) {
		$sql = "SELECT * FROM roles WHERE id >= :idRole;";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':idRole', $idRole, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}
  
	public function obtenerCargo($idRole) {
		$sql = "SELECT role FROM roles WHERE id = :idRole;";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':idRole', $idRole, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchColumn();
	}
  
	public function actualizar($sets, $id) 
	{
		$actualizaciones = implode(", ", $sets);
		$sql = "UPDATE usuarios SET {$actualizaciones} WHERE id = {$id}";
		return $this->_db->query($sql)->rowCount();
    }
    
    public function usuario(int $id)
    {
        $sql = "SELECT u.id, u.email, p.alias 
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id 
        WHERE u.id = :id;";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_OBJ);
    }
	
}