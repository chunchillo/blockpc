<?php
/* Clase editarModelo */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class editarModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function usuario(int $id)
    {
        $sql = "SELECT u.id, p.alias, u.email, p.nombre, p.apellido, p.rut, p.telefono, p.celular, p.direccion, p.region, p.provincia, p.comuna
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id 
        WHERE u.deleted_at IS NULL AND u.id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function regiones()
    {
        return $this->_db->query("SELECT * FROM region;")->fetchAll(\PDO::FETCH_OBJ);
    }

    public function provincias(int $region = 0)
    {
        return $this->_db->query("SELECT * FROM provincia WHERE region = {$region};")->fetchAll(\PDO::FETCH_OBJ);
    }

    public function comunas(int $provincia = 0)
    {
        return $this->_db->query("SELECT * FROM comuna WHERE provincia = {$provincia};")->fetchAll(\PDO::FETCH_OBJ);
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
        $perfil['id'] = $id;
        $sqlUsuario = "UPDATE usuarios SET {$setUsuario} WHERE id = :id;";
        $sqlPerfil = "UPDATE perfiles SET {$setPerfil} WHERE user_id = :id;";
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