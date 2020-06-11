<?php
/* ACLModelo.php */
namespace Blockpc\Clases;

class ACLModelo
{
    private $_registro;
    private $_db;

    public function __construct() {
        $this->constructor();
    }
  
    private function constructor() {
        $this->_registro = Registro::getInstancia();
        $this->_db = $this->_registro->get('database');
    }
  
    protected function getModeloRole($id) {
        $consulta = "SELECT role FROM usuarios WHERE id = {$id};";
        $stmt     = $this->_db->query($consulta);
        return $stmt->fetchColumn();
    }
  
    protected function getModeloPermisoRole($role) {
        $consulta = "SELECT * FROM permiso_role WHERE idRole = '{$role}'";
        $stmt     = $this->_db->query($consulta);
        return $stmt->fetchAll();
    }
	
	protected function getModeloPermisosRoleId($idRole) {
        $id = [];
        $consulta = "SELECT idPermiso FROM permiso_role WHERE idRole = '{$idRole}'";
		$stmt     = $this->_db->query($consulta);
		$ids      = $stmt->fetchAll();
		for ( $i = 0; $i < count($ids); $i++ ) {
			$id[] = $ids[$i]['idPermiso'];
		}
		return $id;
	}
  
    protected function getModeloPermisosUsuario($idUsuario, $estosIds) {
        $consulta = "SELECT * FROM permiso_usuario WHERE idUsuario = {$idUsuario} AND idPermiso IN ({$estosIds})";
        $stmt     = $this->_db->query($consulta);
        return $stmt->fetchAll();
    }
  
    protected function getModeloPermisoLlave($permisoId) {
        $consulta  = "SELECT llave FROM permisos WHERE id = {$permisoId}";
		$stmt      = $this->_db->query($consulta);
		return $stmt->fetchColumn();
	}
	
	protected function getModeloPermisoNombre($permisoId) {
        $consulta  = "SELECT permiso FROM permisos WHERE id = {$permisoId}";
		$stmt      = $this->_db->query($consulta);
		return $stmt->fetchColumn();
	}

    protected function cerrarSesion(int $id)
    {
        $sql = "UPDATE usuarios SET enlinea = 0 WHERE activado = 1 AND id = :id";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}