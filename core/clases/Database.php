<?php
/* Database.php */
namespace Blockpc\Clases;

final class Database extends \PDO
{
    public function __construct() {
        try {
			if ( !DB_DSN ) {
				return null;
			}
            parent::__construct(DB_DSN, DB_USER, DB_PASS,
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHAR,
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_FOUND_ROWS   => TRUE,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch(\PDOException $e) {
            throw new ErrorBlockpc("1030/{$e->getMessage()}");
        }
    }
}