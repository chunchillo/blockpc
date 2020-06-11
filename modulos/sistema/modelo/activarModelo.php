<?php
/* Clase activarModelo */
namespace Sistema\Modelo;

use Blockpc\Clases\Modelo;

final class activarModelo extends Modelo {
  
	public function __construct() {
		parent::__construct();
	}

	public function validarCodigo($codigo)
	{
		$sql = "SELECT codigo FROM usuarios WHERE codigo = '{$codigo}';";
		return $this->_db->query($sql)->fetchColumn();
	}

	public function validarUsuario($id, $codigo)
	{
		$sql = "SELECT activado FROM usuarios WHERE id = {$id} AND codigo = '{$codigo}';";
		return $this->_db->query($sql)->fetchColumn();
	}

	public function activarUsuario($id, $nuevoCodigo)
	{
		try {
			$this->_db->beginTransaction();
			$sql = "UPDATE usuarios SET activado = 1, codigo = :nuevoCodigo WHERE id = :id;";
			$this->_db->prepare($sql)->execute([':nuevoCodigo' => $nuevoCodigo, ':id' => $id]);
			return $this->_db->commit();
		} catch(\Exception $e) {
			$this->_db->rollback();
			throw new \Exception($e);
		}
	}
}