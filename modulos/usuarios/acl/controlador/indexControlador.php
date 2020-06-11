<?php
/* Clase indexControlador.php */
namespace Usuarios\Acl\Controlador;

use Blockpc\Clases\Controlador as Controlador;
use Blockpc\Clases\Sesion as Sesion;

final class indexControlador extends Controlador {
  private $_modelo;
  
  public function __construct() {
    $this->construir();
    $this->_modelo = $this->cargarModelo('index');
  }
  
  public function index() {
    try {
      $this->_vista->asignar('titulo', "Lista de Control de Accesos");
      $this->_vista->asignar('subtitulo', "Lista de Control de Accesos | Blockpc");
      $this->_vista->asignar('url_inicio', URL_BASE . 'inicio');
      $this->listasACL();
    } catch(\Exception $e) {
      $error = $this->cargarHTML('error', array('error' => $e->getMessage()));
      $this->_vista->asignar('error', $error);
    }
    $this->cargarPagina($this->_vista->renderizar("index", "acl"));
  }
  
  private function listasACL() {
    $permisos = $this->_modelo->getPermisos();
    $roles = $this->_modelo->getRoles();
    $htmlPermisos = "";
    $contadorPermiso = 0;
    foreach($permisos as $permiso) {
      $permiso['contador'] = ++$contadorPermiso;
      $permiso['editable'] = ($permiso['editable']) ? "Editable" : "No Editable";
      $htmlPermisos .= $this->cargarVista('permisos', $permiso);
    }
    $htmlRoles = "";
    $contadorRole = 0;
    foreach($roles as $role) {
      $role['contador'] = ++$contadorRole;
      $role['editable'] = ($role['editable']) ? "Editable" : "No Editable";
      $htmlRoles .= $this->cargarVista('roles', $role);
    }
    $this->_vista->asignar('permisos', $htmlPermisos);
    $this->_vista->asignar('roles', $htmlRoles);
  }
}