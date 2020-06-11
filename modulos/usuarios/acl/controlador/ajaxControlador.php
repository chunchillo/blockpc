<?php
/* Clase ajaxControlador.php */
namespace Usuarios\Acl\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class ajaxControlador extends Controlador
{
    private $_modelo;
    private $_token;

    public function __construct() {
        $this->construir();
        $this->_token = $this->genToken();
        $this->_modelo = $this->cargarModelo('ajax');
    }
  
    public function index() {
        try {
            $this->_acl->acceso('admin_acces');
            $maximo = $this->_modelo->contarRoles();
            $options = array('options'=>array('default'=>0, 'min_range'=>1, 'max_range'=>$maximo));
            if ( filter_input(INPUT_POST, 'token') != $this->_token ) {
                throw new \Exception("<tr><td colspan='5'>Token Invalido!</td></tr>");
            }
            if ( !filter_input(INPUT_POST, 'idRole', FILTER_VALIDATE_INT, $options) ) {
                throw new \Exception("<tr><td class='text-center' colspan='5'>Debes seleccionar un <b>ROL</b></td></tr>");
            }
            $idRole = filter_input(INPUT_POST, 'idRole', FILTER_VALIDATE_INT, $options);
            $permisos = array_merge($this->getPermisosAll(), $this->getPermisosRole($idRole));
            //echo "<pre>"; print_r($permisos); echo "</pre>"; exit;
            if ( !count($permisos) ) {
                throw new \Exception("<tr><td colspan='5'>No existen permisos para este <b>Rol</b></td></tr>");
            }
            $html = "";
            foreach ( $permisos as $permiso ) {
                $permiso['habilitado'] = ( $permiso['valor'] == "1" ) ? "checked" : "";
                $permiso['denegado']   = ( $permiso['valor'] == "0" ) ? "checked" : "";
                $permiso['ignorado']   = ( $permiso['valor'] === "x" ) ? "checked" : "";
                $html .= $this->cargarVista('tbody', $permiso);
            }
            if ( !$html ) {
                $html = "<tr><td class='text-center' colspan='5'>Debes seleccionar un <b>ROL</b></td></tr>";
            }
            $resultado['ok'] = $idRole;
            $resultado['texto'] = $html;
        } catch(\Exception $e) {
            $resultado['ok'] = 0;
            $resultado['texto'] = $e->getMessage();
        }
        header('Content-Type: application/json; charset=utf-8', true);
        echo json_encode($resultado);
        exit;
    }
  
    private function getPermisosRole($idRole) {
        $permisos = $this->_modelo->getPermisosRole($idRole);
        //echo "<pre>"; print_r($permisos); echo "</pre>"; exit;
        $data = [];
        foreach ( $permisos as $permiso ) {
            $llave = $this->_modelo->getPermisoLlave($permiso['idPermiso']);
            $v = ($permiso['valor']) ?? 0;
            $nombre = $this->_modelo->getPermisoNombre($permiso['idPermiso']);
            $data[$llave] = [
                'llave' => $llave,
                'valor' => ($permiso['valor']) ?? 0,
                'nombre' => $nombre,
                'id' => $permiso['idPermiso']
            ];
        }
        return $data;
    }
  
    private function getPermisosAll() {
        $data = array();
        $permisos = $this->_modelo->datosTabla();
        //echo "<pre>"; print_r($permisos); echo "</pre>"; exit;
        for ( $i = 0; $i < count($permisos); $i++ ) {
            if ( $permisos[$i]['llave'] == '' ) continue;
            $data[$permisos[$i]['llave']] = array(
                'llave' => $permisos[$i]['llave'],
                'valor' => 'x',
                'nombre' => $permisos[$i]['permiso'],
                'id' => $permisos[$i]['id'],
            );
        }
        return $data;
    }
}