<?php
/* Clase indexModelo.php */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo;

final class indexModelo extends Modelo {
  
	public function __construct() {
		parent::__construct();
	}

	public function cargarRoles() {
		$sql = "SELECT * FROM roles";
		return $this->_db->query($sql)->fetchAll();
	}

	public function cargarUsuarios(int $idUsuario) {
		$sql = ( $idUsuario > 1 ) ? "SELECT * FROM usuarios WHERE role > 1 AND activado = 1;" : "SELECT * FROM usuarios WHERE activado = 1;";
		return $this->_db->query($sql)->fetchAll();
	}

	public function obtenerCargo($idRole) {
		$sql = "SELECT role FROM roles WHERE id = :idRole;";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':idRole', $idRole, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}