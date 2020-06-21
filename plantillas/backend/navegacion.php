<?php
/* navegacion.php */
function getMenus(int $sudo = 0) {
	$menus = array();
    /* Menu Lateral */
    // 'admin' => false,
    // 'sudo' => false,
    $menus['laterales'][] = array(
        'id'     => 'dashboard',
        'permiso' => 'general_acces',
        'titulo' => '<i class="fas fa-tachometer-alt mr-1"></i> <span>Dashboard</span>',
        'enlace' => URL_BASE . 'sistema/dashboard'
    );
    $menus['laterales'][] = array(
        'id'     => 'usuarios',
        'permiso' => 'admin_acces',
        'sudo' => false,
        'titulo' => '<i class="fas fa-users mr-1"></i> <span>Usuarios</span>',
        'enlace' => URL_BASE . 'usuarios/activos'
    );
	$menus['laterales'][] = array(
        'id'     => 'usuarios',
        'permiso' => 'sudo_acces',
        'titulo' => '<i class="fa fa-users mr-1"></i> <span>Usuarios</span>',
        'enlace' => '#',
        'submenus' => [
            '<i class="fa fa-users mr-1"></i> Usuarios' => URL_BASE . 'usuarios/activos',
            '<i class="fa fa-cog mr-1"></i> Permisos' => URL_BASE . 'usuarios/permisos/listar',
            '<i class="fa fa-cog mr-1"></i> Roles' => URL_BASE . 'usuarios/roles/listar',
            '<i class="fa fa-cogs mr-1"></i> ACL' => URL_BASE . 'usuarios/acl/control',
        ]
    );
	
    /* Menu Superior */
	
    $menus['superior'][] = array(
        'id'     => 'home',
        'permiso' => 'general_acces',
        'icono' => '<i class="fa fa-home" aria-hidden="true"></i>',
        'titulo' => 'Home',
        'enlace' => URL_BASE
    );

    // if ( $sudo == 1 ) {
    //     echo "<pre>"; print_r($menus); echo "</pre>"; exit;
    // }
    
    return $menus;
}