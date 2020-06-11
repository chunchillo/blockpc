<?php
/* Clase nuevoModelo */
namespace Usuarios\Index\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class nuevoModelo extends Modelo {

    public function __construct() {
        parent::__construct();
    }

    public function roles()
    {
        return $this->_db->query("SELECT * FROM roles;")->fetchAll(\PDO::FETCH_OBJ);
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

    public function checkEmail( string $email ): int
    {
        return $this->_db->query("SELECT id FROM usuarios WHERE email = '{$email}';")->fetchColumn();
    }

    public function checkAlias( string $alias ): int
    {
        return $this->_db->query("SELECT id FROM perfiles WHERE alias = '{$alias}';")->fetchColumn();
    }

    public function checkRut( string $rut ): int
    {
        return $this->_db->query("SELECT id FROM perfiles WHERE rut = '{$rut}';")->fetchColumn();
    }

    public function nuevo(array $usuario, array $perfil): int
    {
        $columnas = array_keys($perfil);
        $placeholders = [];
        foreach( $perfil as $key => $value) {
            $placeholders[] = ":{$key}";
        }
        $valores = implode(", ", $placeholders);
        $set = implode(", ", $columnas);
        $sqlNuevoUsuario = "INSERT INTO usuarios (email, clave, role, codigo) VALUES (:email, :clave, :role, :codigo);";
        $sqlNuevoPerfil = "INSERT INTO perfiles ({$set}) VALUES ({$valores});";
        try {
            $this->_db->beginTransaction();
            $stmt = $this->_db->prepare($sqlNuevoUsuario);
            $stmt->execute($usuario);
            $user_id = $this->_db->lastInsertId();
            $perfil['user_id'] = $user_id;
            $stmt = $this->_db->prepare($sqlNuevoPerfil);
            $stmt->execute($perfil);
            if ( $this->_db->commit() ) {
                return $user_id;
            }
        } catch(\Exception $e) {
            if ($this->_db->inTransaction()) {
                $this->_db->rollback();
            }
            throw $e;
        }
    }

    public function usuario(int $id)
    {
        $sql = "SELECT u.id, DATE(u.created_at) AS creado, p.alias, u.email, p.nombre, p.apellido, p.rut, p.telefono, p.celular, p.direccion, rg.nombre AS region, pr.nombre AS provincia, c.nombre AS comuna, r.role
        FROM usuarios u 
        LEFT JOIN perfiles p ON p.user_id = u.id 
        LEFT JOIN roles r ON r.id = u.role 
        LEFT JOIN region rg ON rg.id = p.region  
        LEFT JOIN provincia pr ON pr.id = p.provincia 
        LEFT JOIN comuna c ON c.id = p.comuna 
        WHERE u.deleted_at IS NULL AND u.id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
}