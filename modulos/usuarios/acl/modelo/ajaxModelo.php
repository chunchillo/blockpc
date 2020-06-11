<?php
/* Clase ajaxModelo */
namespace Usuarios\Acl\Modelo;

use Blockpc\Clases\Modelo;

final class ajaxModelo extends Modelo
{
  
    public function __construct() {
        parent::__construct();
    }

    public function contarRoles() {
        return $this->_db->query("SELECT MAX(id) FROM roles")->fetchColumn();
    }
  
    public function getPermisosRole($idRole) {
        if ( $idRole > 1 ) {
            $sql = "SELECT * FROM permiso_role WHERE idRole = :idRole AND idPermiso > 1;";
        } else {
            $sql = "SELECT * FROM permiso_role WHERE idRole = :idRole;";
        }
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':idRole' => $idRole]);
        return $stmt->fetchAll();
    }
  
    public function getPermisoLlave($idPermiso) {
        $sql = "SELECT SUBSTRING_INDEX(llave, '_', 1) AS llave FROM permisos WHERE id = :idPermiso";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':idPermiso' => $idPermiso]);
        return $stmt->fetchColumn();
    }

	public function getPermisoNombre($idPermiso) {
		$sql = "SELECT permiso FROM permisos WHERE id = :idPermiso";
		$stmt = $this->_db->prepare($sql);
		$stmt->execute([':idPermiso' => $idPermiso]);
        return $stmt->fetchColumn();
	}
  
    public function datosTabla() {
        $sql = "SELECT id, permiso, SUBSTRING_INDEX(llave, '_', 1) AS llave, descripcion, editable FROM permisos WHERE id > 1;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchall();
    }
}