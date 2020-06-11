<?php
/* Clase editarModelo.php */
namespace Usuarios\Perfil\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class editarModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function usuario($id) {
        $sql = "SELECT u.id, u.email, u.clave, DATE(u.created_at) AS creado, u.role, r.role AS cargo, u.activado, p.alias, p.nombre, p.apellido, p.rut, p.telefono, p.celular, p.direccion, p.region, p.provincia, p.comuna, p.resumen, p.imagen 
		FROM usuarios u 
		INNER JOIN perfiles p ON p.user_id = u.id 
		INNER JOIN roles r ON r.id = u.role 
		WHERE u.id = {$id};";
        return $this->_db->query($sql)->fetch();
    }

    public function checkEmail( string $email, int $id ): int
    {
        return $this->_db->query("SELECT id FROM usuarios WHERE email = '{$email}' AND id != {$id};")->fetchColumn();
    }

    public function checkAlias( string $alias, int $id ): int
    {
        return $this->_db->query("SELECT id FROM perfiles WHERE alias = '{$alias}' AND user_id != {$id};")->fetchColumn();
    }

    public function checkRut( string $rut, int $id ): int
    {
        return $this->_db->query("SELECT id FROM perfiles WHERE rut = '{$rut}' AND user_id != {$id};")->fetchColumn();
    }
	
    public function region() {
        $sql = "SELECT * FROM region";
		return $this->_db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
    }

    public function provincia(int $region) {
        $sql = "SELECT * FROM provincia WHERE region = :region";
		$stmt = $this->_db->prepare($sql);
		$stmt->execute(['region' => $region]);
		return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function comuna(int $provincia) {
        $sql = "SELECT * FROM comuna WHERE provincia = :provincia";
		$stmt = $this->_db->prepare($sql);
		$stmt->execute(['provincia' => $provincia]);
		return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function actualizar(array $usuario, array $perfil, int $id)
    {
        foreach( $usuario as $k => $v ) {
            $phUsuario[] = "{$k} = :{$k}";
        }
        $setUsuario = implode(", ", $phUsuario);
        foreach( $perfil as $k => $v ) {
            $phPerfil[] = "{$k} = :{$k}";
        }
        $setPerfil = implode(", ", $phPerfil);
        $usuario['id'] = $id;
        $perfil['user_id'] = $id;
        $sqlUsuario = "UPDATE usuarios SET {$setUsuario} WHERE id = :id;";
        $sqlPerfil = "UPDATE perfiles SET {$setPerfil} WHERE user_id = :user_id;";
        try {
            $this->_db->beginTransaction();
            $contador = 0;
            $stmt = $this->_db->prepare($sqlUsuario);
            $stmt->execute($usuario);
            $contador += $stmt->rowCount();
            $stmt = $this->_db->prepare($sqlPerfil);
            $stmt->execute($perfil);
            $contador += $stmt->rowCount();
            if ( $this->_db->commit() ) {
                return $contador;
            }
        } catch(\Exception $e) {
            if ($this->_db->inTransaction()) {
                $this->_db->rollback();
            }
            throw $e;
        }
    }
  
}