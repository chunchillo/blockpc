<?php
/* Clase indexModelo.php */
namespace Usuarios\Perfil\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class indexModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }
    
    public function buscarUsuario($usuario) {
        $sql = "SELECT u.usuario, u.nombre, u.correo, r.role as cargo 
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id 
        INNER JOIN roles r ON r.id = u.role
        WHERE u.usuario = :usuario;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function obtenerCargo($idRole) {
        $sql = "SELECT role FROM roles WHERE id = :idRole;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':idRole', $idRole, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
	
	public function truncar()
	{
        return true;
		// $sqls = [
		// 	"DELETE FROM `empresas` WHERE tipo_id IN (2,3); ",
		// 	"ALTER TABLE `empresas` AUTO_INCREMENT = 6; ",
		// 	"TRUNCATE `contactos`;",
		// 	"ALTER TABLE `contactos` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `contactos_empresa`;",
		// 	"ALTER TABLE `contactos_empresa` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `contratos`;",
		// 	"ALTER TABLE `contratos` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `homologa_sucursal`;",
		// 	"ALTER TABLE `homologa_sucursal` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `importacion`;",
		// 	"ALTER TABLE `importacion` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `msg_importacion`;",
		// 	"ALTER TABLE `msg_importacion` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `servicios`;",
		// 	"ALTER TABLE `servicios` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `stock_vehiculos`;",
		// 	"ALTER TABLE `stock_vehiculos` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `ventas`;",
		// 	"ALTER TABLE `ventas` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `inventario`;",
		// 	"ALTER TABLE `inventario` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `orden_trabajo`;",
		// 	"ALTER TABLE `orden_trabajo` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `detalle_pago_mandantes`;",
		// 	"ALTER TABLE `detalle_pago_mandantes` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `estados_pago_ejecutivo`;",
		// 	"ALTER TABLE `estados_pago_ejecutivo` AUTO_INCREMENT = 1; ",
		// 	"TRUNCATE `estados_pago_mandantes`;",
		// 	"ALTER TABLE `estados_pago_mandantes` AUTO_INCREMENT = 1; ",
		// 	"UPDATE ejecutivos SET pend = 0, cva = 0, ejec = 0, cve = 0, um = 0, u12m = 0, tac = 0; ",
		// ];
		// try {
		// 	$this->_db->beginTransaction();
			
		// 	foreach($sqls as $sql ) {
		// 		$stmt = $this->_db->prepare($sql);
		// 		$stmt->execute();
		// 	}
		// 	$this->delete_files(RUTA_ARCHIVOS . "temp");
			
		// 	if ( $this->_db->commit() ) {
		// 		return true;
		// 	}
		// } catch(\Exception $e) {
		// 	if ($this->_db->inTransaction()) {
		// 		$this->_db->rollback();
		// 	}
		// 	throw $e;
		// }
	}
	
	private function delete_files($target, $level = 0, $deep = 0)
	{
		if( is_dir($target) ) {
			$deep++;
			$files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
			foreach( $files as $file ) {
				$this->delete_files( $file, $level, $deep );
			}
			if ( $level && $deep >= $level ) {
				@rmdir( $target );
			}
		} else if( $deep > $level && is_file($target) ) {
			unlink( $target );  
		}
	}
  
}