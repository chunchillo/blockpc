<?php
/* Clase salirModelo.php */
namespace Sistema\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class salirModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function salir(int $id)
    {
        $sql = "UPDATE usuarios SET enlinea = 0 WHERE activado = 1 AND id = :id";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
  
}