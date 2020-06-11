<?php
/* Clase activosModelo */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo;

final class activosModelo extends Modelo {

	public function __construct() {
		parent::__construct();
	}

    public function usuarios(int $idUsuario, string $searchQuery, array $searchArray, string $orderQuery)
    {
        $where = " AND u.activado = 1 AND u.deleted_at IS NULL ";
        if ( $idUsuario > 1 ) {
            $where = " AND u.role > 1 AND u.activado = 1 AND u.deleted_at IS NULL ";
        }
        $sql = "SELECT u.id, p.alias AS usuario, CONCAT(IFNULL(p.nombre, '--'), ' ', IFNULL(p.apellido, '--')) as nombres, u.email, IFNULL(p.rut, '--') as rut, IFNULL(p.telefono, '--') as telefono, r.role AS cargo
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id
        LEFT JOIN roles r ON r.id = u.role
        WHERE 1 {$searchQuery} {$where} {$orderQuery}";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($searchArray);
        return $stmt->fetchAll();
	}
}