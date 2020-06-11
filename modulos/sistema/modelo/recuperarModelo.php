<?php
/* Clase recuperarModelo */
namespace Sistema\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class recuperarModelo extends Modelo {
  
	public function __construct() {
		parent::__construct();
	}

	public function recuperarUsuario($usuario, $email)
	{
		$sql = "SELECT id, usuario, nombre, email, codigo, activado FROM usuarios WHERE usuario = :usuario AND email = :email;";
		$stmt = $this->_db->prepare($sql);
		$stmt->execute([':usuario' => $usuario, ':email' => $email]);
		return $stmt->fetch();
	}

	public function actualizarDatosUsuario($id, $clave)
	{
		try {
			$this->_db->beginTransaction();
			$sql = "UPDATE usuarios SET activado = 0, clave = :clave WHERE id = :id;";
			$this->_db->prepare($sql)->execute([':clave' => $clave, ':id' => $id]);
			return $this->_db->commit();
		} catch(\Exception $e) {
			$this->_db->rollback();
			throw new \Exception($e);
		}
	}
}