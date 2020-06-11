<?php
/* Clase TablaBlockpc.php */
namespace Blockpc\Librerias;

final class TablaBlockpc {
	
	private $_tabla;
	private $_url;
	private $_directorio;
	private $_tbody;
	private $_leyenda;
	private $_paginador;
	private $_registros;
	private $_cols;
	private $_registrosPorPagina;
	private $_totalRegistros;
	private $_numeroPaginas;
	private $_vistaTabla;
	private $_vistaPaginador;
	private $_permiso;
	private $_aliasPaginador;

	public function __construct(string $tabla = null, int $columnas = null) {
		$this->_tabla = $tabla ?? 'Registros';
		$this->_directorio = __DIR__ . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR;
		$this->_tbody = '';
		$this->_leyenda = '';
		$this->_paginador = '';
		$this->_registros = [];
		$this->_cols = $columnas ?? 1;
		$this->_registrosPorPagina = 10;
		$this->_totalRegistros = 10;
		$this->_numeroPaginas = 0;
		$this->_vistaTabla = $this->_directorio . 'tabla.phtml';
		$this->_vistaPaginador = $this->_directorio . 'paginador.phtml';
		$this->_vistaBotonera = $this->_directorio . 'botonera.phtml';
		$this->_permiso = true;
		$this->_aliasPaginador = 'registro';
	}
	
	public function setUrlBase(string $url = null)
	{
		$this->_url = $url ?? URL_BASE . mb_strtolower($this->_tabla);
	}
	
	public function setRegistrosPorPagina(int $valor = 10)
	{
		if ( !filter_var($valor, FILTER_VALIDATE_INT) ) {
			throw new \Exception("El valor de <b>Registros Por Pagina</b> debe ser entero!");
		}
		$this->_registrosPorPagina = $valor;
	}
	
	public function setRegistros(array $registros)
	{
		if( !is_array($registros) ) {
			throw new \Exception("Se esperaba un arreglo como parámetro!");
		}
		$this->_registros = $registros;
		if ( !$this->_totalRegistros = count($this->_registros) ) {
			$cols = $this->_cols;
			$this->_leyenda = "No hay registros en <b>{$this->_tabla}</b>";
			$this->_tbody = "<tr><td colspan='{$cols}'>Sin <b>{$this->_tabla}</b> que mostrar</td></tr>";
			$this->_paginador = '';
		}
		$contador = 1;
		foreach( $this->_registros as &$registro ) {
			$registro['contador'] = $contador++;
		}
		$this->_numeroPaginas = ceil($this->_totalRegistros/$this->_registrosPorPagina);
	}
	
	public function getRegistros()
	{
		return $this->_registros;
	}
	
	public function setVistaTabla(string $vista = null)
	{
		$this->_vistaTabla = $vista ?? $this->_directorio . 'tabla.phtml';
	}
	
	public function setVistaPaginador(string $vista = null)
	{
		$this->_vistaPaginador = $vista ?? $this->_directorio . 'paginador.phtml';
	}
	
	public function setVistaBotonera(string $vista = null)
	{
		$this->_vistaBotonera = $vista ?? $this->_directorio . 'botonera.phtml';
	}
	
	public function setPermiso(bool $permiso = null)
	{
		$this->_permiso = $permiso ?? true;
	}
	
	public function setAliasPaginador(string $aliasPaginador = null)
	{
		$this->_aliasPaginador = $aliasPaginador ?: 'registro';
	}
	
	public function procesar(int $pagina = 1)
	{
		# Antes de procesar....
		if ( !$this->_totalRegistros ) {
			return $this;
		}
		#
		$offset = ( $pagina <= 1 ) ? 0 : ($pagina - 1) * $this->_registrosPorPagina;
		$registrosAMostrar = array_slice($this->_registros, $offset, $this->_registrosPorPagina, true);
		foreach( $registrosAMostrar as $registro ) {
			$this->_tbody .= $this->cargarVista($this->_vistaTabla, $registro);
		}
		if ( !$this->_tbody ) {
			$this->_tbody = "<tr><td colspan='{$this->_cols}'>Sin <b>{$this->_tabla}</b> que mostrar</td></tr>";
		}
		# Leyenda
		$total = count($this->_registros);
		$primero = $offset + 1;
		$ultimo = ($pagina * $this->_registrosPorPagina > $total) ? $total : $pagina * $this->_registrosPorPagina;
		$this->_leyenda = ($total) ? "Mostrando <b>{$this->_tabla}</b> desde el <b>{$primero}</b> al <b>{$ultimo}</b> de <b>{$total}</b>." : "No hay registros en <b>{$this->_tabla}</b>";
		# Paginación
		$botonera = '';
		if ( $pagina <= $this->_numeroPaginas ) {
			$inicio = ( ($pagina - 2) >= 1 ) ? $pagina - 2 : 1;
			$fin = ( $this->_numeroPaginas >= ($pagina + 2) ) ? $pagina + 2 : $this->_numeroPaginas;
		}
		for($i = $inicio; $i <= $fin; $i++) {
			$arreglo = [
				'tabla' => $this->_aliasPaginador,
				'actual' => ( $pagina == $i ) ? 'active' : '',
				'href' => ( $pagina == $i ) ? '#' : $this->_url . "{$i}",
				'valor' => $i,
			];
			$botonera .= $this->cargarVista($this->_vistaBotonera, $arreglo);
		}
		if ( $pagina <= $this->_numeroPaginas ) {
			$anterior = ( ($pagina - 1) >= 1 ) ? $pagina - 1 : 1;
			$siguiente = ( $this->_numeroPaginas >= ($pagina + 1) ) ? $pagina + 1 : $this->_numeroPaginas;
		}
		$paginacion = [
			'tabla' => $this->_aliasPaginador,
			'no_primero' => ($inicio == 1) ? 'disabled' : '',
			'primero' => $this->_url . "1",
			'registro_primero' => 1,
			'no_anterior' => ($anterior == 1) ? 'disabled' : '',
			'anterior' => $this->_url . "{$anterior}",
			'registro_anterior' => $anterior,
			'botonera' => $botonera,
			'registro_siguiente' => $siguiente,
			'siguiente' => $this->_url . "{$siguiente}",
			'no_siguiente' => ($siguiente == $this->_numeroPaginas) ? 'disabled' : '',
			'registro_ultimo' => $this->_numeroPaginas,
			'ultimo' => $this->_url . "{$this->_numeroPaginas}",
			'no_ultimo' => ($fin == $this->_numeroPaginas) ? 'disabled' : '',
		];
		$this->_paginador = ($total) ? $this->cargarVista($this->_vistaPaginador, $paginacion) : '';
	}
	
	private function cargarVista(string $vista, array $variables = [])
    {
        if ( !is_readable($vista) ) {
            throw new \Exception("Vista <b>{$vista}</b> no encontrada");
        }
        ob_start();
        include $vista;
        $pagina = ob_get_contents();
        ob_get_clean();
        return $this->reemplazar($pagina, $variables);
    }
	
	private function reemplazar($buffer, $variables = array())
    {
        if ( count($variables) ) {
            foreach($variables as $clave => $valor) {
                $pos = strpos($buffer, '[' . strtoupper($clave) . ']');
                if ( $pos !== FALSE ) {
                    $buffer = str_replace('[' . strtoupper($clave) . ']', $valor, $buffer);
                }
            }
        }
        return $buffer;
    }
	
	public function getTbody()
	{
		return $this->_tbody;
	}
	
	public function getLeyenda()
	{
		return $this->_leyenda;
	}
	
	public function getPaginador()
	{
		return $this->_paginador;
	}
	
	public function getCarpeta()
	{
		return $this->_directorio;
	}
}