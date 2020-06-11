<?php
/* Clase listarModelo */
namespace Usuarios\Roles\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class listarModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function roles(int $id, string $searchQuery, array $searchArray, string $orderQuery): array
    {
        if ( $id > 1 ) {
            $sql = "SELECT r.id, r.role, r.descripcion, r.editable, COUNT(u.id) AS total,
            sum(case when u.activado = 1 AND u.deleted_at IS NULL then 1 else 0 end) AS activados,
            sum(case when u.activado = 0 AND u.deleted_at IS NULL then 1 else 0 end) AS noactivados, 
            sum(case when u.deleted_at IS NOT NULL then 1 else 0 end) AS eliminados 
            FROM roles r 
            LEFT JOIN usuarios u ON u.role = r.id 
            WHERE r.id > 1 {$searchQuery} {$orderQuery}";
        } else {
            $sql = "SELECT r.id, r.role, r.descripcion, r.editable, COUNT(u.id) AS total,
            sum(case when u.activado = 1 AND u.deleted_at IS NULL then 1 else 0 end) AS activados,
            sum(case when u.activado = 0 AND u.deleted_at IS NULL then 1 else 0 end) AS noactivados, 
            sum(case when u.deleted_at IS NOT NULL then 1 else 0 end) AS eliminados 
            FROM roles r 
            LEFT JOIN usuarios u ON u.role = r.id 
            WHERE 1 {$searchQuery} {$orderQuery}";
        }
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($searchArray);
        return $stmt->fetchAll();
    }
}