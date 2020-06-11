<?php
/* Clase indexControlador.php */
namespace Usuarios\Roles\Controlador;

use Blockpc\Clases\Controlador as Controlador;

final class indexControlador extends Controlador {
  
  public function __construct() {
    $this->construir();
    $this->_vista->asignar('error', '');
    $this->_vista->asignar('mensaje', '');
  }
  
  public function index() {
    try {
      $this->redireccionar('usuarios/roles/listar');
    } catch(\Exception $e) {
      $error = $this->cargarVista('error', array('error' => $e->getMessage()));
      $this->_vista->asignar('error', $error);
    }
    $this->cargarPagina($this->_vista->renderizar("index", "roles"));
  }
}