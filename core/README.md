## Directorio CORE

Almacena las clases esenciales del framework y las librerias propias o de terceros
Contiene el archivo de Configuración

## configuracion.php

Archivo de configuración base del framework.
Contiene una serie de constantes que definen el comportamiento del framework.

## dev.php
Archivo de configuracion para desarrollo
Se debe crear con el siguiente contenido
```
<?php
define('WEB_DEV', 'domain.tld');
define('DB_NAME_DEV', 'db_name');
define('DB_USER_DEV', 'db_user');
define('DB_PASS_DEV', 'db_user');
?>