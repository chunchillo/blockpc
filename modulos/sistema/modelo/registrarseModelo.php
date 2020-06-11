<?php
/* Clase registrarseModelo.php */
namespace Sistema\Modelo;

use Blockpc\Clases\Modelo;

final class registrarseModelo extends Modelo
{
    public function __construct() {
        parent::__construct();
    }

    public function registrar(array $usuario, array $perfil): int
    {
        $sqlNuevoUsuario = "INSERT INTO usuarios (email, clave, role, codigo) VALUES (:email, :clave, :role, :codigo);";
        $sqlNuevoPerfil = "INSERT INTO perfiles (alias, user_id) VALUES (:alias, :user_id);";
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

    public function checkEmail( string $email ): int
    {
        return $this->_db->query("SELECT id FROM usuarios WHERE email = '{$email}';")->fetchColumn();
    }
}