<?php
/* Clase exportarControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\ErrorBlockpc;
use Blockpc\Clases\Sesion;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class exportarControlador extends Controlador
{
    private $_modelo;
    private $_token;
	private $_funciones;
	private $_hoy;
	private $_fecha;

    public function __construct() {
        $this->construir();
        //$this->_modelo = $this->cargarModelo('exportar');
        $this->_token = $this->genToken();
		$this->_funciones = $this->cargarLibreria('Funciones');
		$this->_hoy = date("Y-m-d");
		$this->_fecha = $this->_funciones->Fecha($this->_hoy);
    }
	
	public function index()
	{
		$this->redireccionar('sistema/dashboard');
	}
	
	public function printer()
	{
		try {
			$this->_acl->acceso('general_acces');
			if ( $this->_token != $this->post('token') ) {
				throw new \Exception("Llave no valida!");
			}
			$resultado['ok'] = true;
			$resultado['mensaje'] = "Printer data";
		} catch(\Exception $e) {
			$resultado['ok'] = false;
			$resultado['error'] = $e->getMessage();
		}
		header('Content-Type: application/json; charset=utf-8', true);
		echo json_encode($resultado);
		exit;
	}
	
	public function pdf()
	{
		$filename = "usuarios";
		$dompdf = new Dompdf();
		$data = Sesion::get('data');
		$html = '';
		$tbody = '';
		$contador = 1;
		foreach ( $data as $line ) {
			$line['contador'] = $contador;
			$tbody .= $this->cargarVista('pdf_users_tbody', $line);
			$contador++;
		}
		if ( $tbody ) {
			$html = $this->cargarVista('pdf_users', ['tbody' => $tbody, 'fecha' => $this->_fecha]);
		}
		$dompdf->set_base_path(RUTA_ASSETS);
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream($filename);
	}
	
	public function word()
	{
		$filename = 'usuarios.docx';
		try {
			$this->_acl->acceso('general_acces');
			if ( $this->_token != $this->post('token') ) {
				throw new \Exception("Llave no valida!");
			}
			$resultado['ok'] = true;
			$resultado['mensaje'] = "Exportando a Word";
		} catch(\Exception $e) {
			$resultado['ok'] = false;
			$resultado['error'] = $e->getMessage();
		}
		header('Content-Type: application/json; charset=utf-8', true);
		echo json_encode($resultado);
		exit;
	}
	
	public function excel()
	{
		try {
			$filename = 'usuarios.xlsx';
			$data = Sesion::get('data');
			$keys = array_keys($data[0]);
			$cols = count($keys);
			$rows = count($data);
			$reader = IOFactory::createReader('Xlsx');
			// $spreadsheet = new Spreadsheet();
			$spreadsheet = $reader->load(RUTA_ASSETS . 'pdf' . DS . 'reporte_usuarios.xlsx');
			$spreadsheet->setActiveSheetIndex(0);
			$spreadsheet->getProperties()
						->setCreator("controlando.cl")
						->setLastModifiedBy('controlando.cl')
						->setTitle('Excel Usuarios')
						->setSubject('Excel de Usuarios')
						->setDescription('Excel generado para Usuarios')
						->setKeywords('Usuarios')
						->setCategory('Usuarios');
			$contentStartRow = 4;
			$currentContentRow = 4;
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setTitle("Usuarios");
			$sheet->setCellValue('E1', $this->_fecha);
			$sheet->insertNewRowBefore($currentContentRow+1, $rows);
			$sheet->fromArray($data, NULL, 'A4');
			$sheet->removeRow($contentStartRow + $rows + 2, 2);
			/* 
			foreach (range(1, $cols) as $i) {
				$sheet->setCellValueByColumnAndRow($i, 1, $keys[$i-1]);
			}
			foreach (range(2, $rows) as $row) {
				$fila = $data[$row-2];
				foreach (range(1, $cols) as $col) {
					$columna = $keys[$col-1];
					$sheet->setCellValueByColumnAndRow($col, $row, $fila[$columna]);
				}
			} */
			header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
			header("Content-Disposition: attachment;filename={$filename}");
			header("Cache-Control: max-age=0");
			header('Cache-Control: max-age=1');
			header('Cache-Control: cache, must-revalidate');
			header('Pragma: public');
			$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
			$writer->save('php://output');
			exit();
		} catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
			new ErrorBlockpc("1000/{$e->getMessage()}");
		}
	}
	
	public function csv()
	{
		$filename = "usuarios.csv";
		$data = Sesion::get('data');
		header( 'Content-Type: text/csv, charset=utf8' );
		header( 'Content-Disposition: attachment; filename='.$filename);
		$out = fopen('php://output', 'w');
		fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		fputcsv($out, array_keys($data[0]));
		foreach ( $data as $line ) {
            fputcsv($out, $line);
        }
		fclose($out);
		exit;
	}
	
}