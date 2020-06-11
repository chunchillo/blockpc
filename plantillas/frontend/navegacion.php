<?php
/* navegacion.php */
function getMenus() {
	$menus = [
		[
		'id'     => 'inicio',
		'titulo' => '<i class="fas fa-xs fa-home mr-1" aria-hidden="true"></i> Inicio',
		'enlace' => URL_BASE
		],
		[
		'id'     => 'login',
		'titulo' => 'Login',
		'enlace' => URL_BASE . 'sistema/login'
		]
	];
	return $menus;
}