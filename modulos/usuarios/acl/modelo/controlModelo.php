<?php
/* Clase controlModelo */
namespace Usuarios\Acl\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class controlModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function cargarRoles(int $rol) {
        $sql = "SELECT * FROM roles WHERE id >= :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(["id" => $rol]);
        return $stmt->fetchAll();
    }
  
    public function buscarRoleID($idRole) {
        return $this->_db->query("SELECT role FROM roles WHERE id = {$idRole}")->fetchColumn();
    }
  
    public function contarPermisos() {
        return $this->_db->query("SELECT COUNT(id) FROM permisos")->fetchColumn();
    }
  
    public function buscarPermisoID($llave) {
        $llave = "{$llave}_acces";
        $sql = "SELECT id FROM permisos WHERE llave = :llave";
        $stmt = $this->_db->prepare($sql);
        $valores = array(':llave' => $llave);
        $stmt->execute($valores);
        return $stmt->fetchColumn();
    }
  
    public function editarPermisoRole($valores) {
        $sql = "REPLACE INTO permiso_role SET idRole = :idRole, idPermiso = :idPermiso, valor = :valor;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($valores);
        return $stmt->rowCount();
    }

    public function eliminarPermisoRole($valores) {
        $sql = "DELETE FROM permiso_role WHERE idRole = :idRole AND idPermiso = :idPermiso;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($valores);
        return $stmt->rowCount();
    }
}