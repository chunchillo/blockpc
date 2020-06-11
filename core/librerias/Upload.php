<?php /* Clase Upload.php */
namespace Blockpc\Librerias;

class Upload {
	
	private $_exts;
	private $_arreglo;
	private $_altoPermitido;
	private $_anchoPermitido;
	private $_pesoPermitido;
	private $_pesoNorma;
	
	private $_fileError;
	private $_fileName;
	private $_fileSize;
	private $_fileTmp;
	private $_fileType;
	
	private $_directorio;
	private $_nombre;
	private $_ancho;
	private $_alto;
	private $_ext;
	private $_error;
	
	private $_ds = DIRECTORY_SEPARATOR;
	
	public function __construct() {
        $this->_exts 	= ['jpg', 'png', 'gif',];
        $this->_arreglo        = array();
        $this->_altoPermitido  = 640; 		# Alto máximo permitido
        $this->_altoMinimo 	   = 300; 		# Alto mínimo permitido
        $this->_anchoPermitido = 800; 		# Ancho Máximo permitido
        $this->_anchoMinimo    = 300; 		# Ancho mínimo permitido
        $this->_pesoPermitido  = 2000000; 	# Peso máximo permitido
        $this->_pesoNorma      = "2Mb"; 	# Norma Peso Máximo permitido
		$this->_fileError	= null;
		$this->_fileName	= null;
		$this->_fileSize	= null;
		$this->_fileTmp		= null;
		$this->_fileType	= null;
		$this->_directorio	= null;
		$this->_nombre		= null;
		$this->_ancho		= null;
		$this->_alto		= null;
		$this->_ext			= null;
		$this->_error		= null;
    }
  
    public function setArchivo($archivo = null) {
        if ( !$this->_archivo = $archivo ) {
			throw new \Exception("No se ha subido ningún archivo!");
		}
		$this->setPropiedades();
    }
	
	public function setNombre($nombre = null, $caracteres = 0)
	{
		if ( $caracteres && mb_strlen($nombre) > $caracteres ) {
			throw new \Exception("El nombre <b<{$nombre}</b> parece superar los {$caracteres} caracteres!");
		}
		if ( !$nombre ) {
			throw new \Exception("El nombre de la imagen no puede ser nulo!");
		}
		$this->_nombre = $this->procesarNombre($nombre);
	}
  
    public function setDirectorio($ruta) {
        if ( !is_dir($ruta) ) {
            if ( !mkdir($ruta, 0755, true) ) {
                throw new \Exception("Directorio no valido. Ruta: <b>{$ruta}</b>");
            }
        }
        $this->_directorio = $ruta . $this->_ds;
    }
	
	public function setPesoMinimo(int $peso) {
        # Asignamos el peso mínimo de la imagen
		# En bits, ejemplo 1000 = 1kb, 1000000 = 1 Mb
        $this->_pesoPermitido = $peso;
        $base = log($peso) / log(1024);
        $suffix = array("B", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        $this->_pesoNorma = round(pow(1024, $base - floor($base)), 0, PHP_ROUND_HALF_UP) . $suffix[$f_base];
    }
  
    public function setAnchoAlto(int $ancho, int $alto) {
        # Asignamos los tamaños minimos permitidos
        $this->_altoPermitido  = $alto;
        $this->_anchoPermitido = $ancho;
    }
  
    public function setAnchoAltoMinimos(int $ancho, int $alto) {
        # Asignamos los tamaños minimos permitidos
        $this->_altoMinimo  = $alto;
        $this->_anchoMinimo = $ancho;
    }
  
    public function setExtensiones(string ...$extensiones) {
        $this->_exts = null;
        foreach ($extensiones as $extension) {
            $this->_exts[] = $extension;
        }
        //return $this->validaExtension();
    }
	
	public function guardar()
	{
		if ( $this->validaciones() ) {
			$nombre = $this->getNombre();
			$archivo = basename($this->_fileName);
			$temporal = $this->_fileTmp;
			$destino = $this->_directorio . $archivo;
			move_uploaded_file($temporal, $destino);
			@rename($destino, $this->_directorio . $nombre);
			if ( !file_exists( $this->_directorio . $nombre ) )
				throw new \Exception("No se consiguió guardar el archivo");
			return true;
		}
	}
	
	public function simular()
	{
		if ( $this->validaciones() ) {
			rmdir($this->_directorio);
			return true;
		}
	}
	
	public function eliminar(string $rutaImagenAntigua = null)
	{
		[ 'basename' => $basename, 'dirname' => $dirname, 'extension' => $extension, 'filename' => $filename ] = pathinfo($rutaImagenAntigua);
		if ( !$filename ) {
			throw new \Exception("El archivo parece no tener un nombre valido!");
		}
		if ( !$extension ) {
			throw new \Exception("El archivo parece no tener una extensión valido!");
		}
		if ( !file_exists(rutaImagenAntigua) ) {
			throw new \Exception("El archivo <b>{$basename}</b> no existe!");
		}
		return $basename;
	}
	
	private function setPropiedades()
    {
        $this->_fileName	= $this->_archivo['name'];
        $this->_fileType 	= $this->_archivo['type'];
        $this->_fileTmp 	= $this->_archivo['tmp_name'];
        $this->_fileSize 	= $this->_archivo['size'];
        $this->_fileError 	= $this->_archivo['error'];
        if ( !isset($this->_fileError) || is_array($this->_fileError) ) {
            throw new \Exception('Invalid parameters.');
        }
        @list($this->_ancho, $this->_alto) 	= getimagesize($this->_fileTmp);
        $this->_ext = pathinfo($this->_fileName, PATHINFO_EXTENSION);
        $this->_nombre = pathinfo($this->_fileName, PATHINFO_FILENAME);
		$this->_nombre = $this->procesarNombre($this->_nombre);
		$this->_error = $this->setError();
    }
	
	private function procesarNombre(string $nombre)
	{
		$nombre = filter_var($nombre, FILTER_SANITIZE_STRING);
		$nombre = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$nombre);
		$nombre = preg_replace("#[[:punct:]]#", " ", trim($nombre));
		$nombre = preg_replace('/\s+/', ' ',$nombre);
		$nombre = trim($nombre);
		$nombre = mb_strtolower(str_replace(" ", "-", $nombre), 'UTF-8');
		return $nombre;
	}
	
	private function validaciones()
	{
		$errores = [];
		if ( !$this->_directorio ) {
			$errores[] = "No existe un directorio asociado para guardar el archivo!";
			//throw new \Exception("No existe un directorio asociado para guardar el archivo!");
		}
		if ( (mb_strlen($this->_nombre, "UTF-8") > 225) ) {
			$errores[] = "El nombre del archivo es muy largo. Archivo <b>{$this->_nombre}</b>";
            //throw new \Exception("El nombre del archivo es muy largo. Archivo <b>{$this->_nombre}</b>");
        }
		if ( $this->_ancho > $this->_anchoPermitido ) {
			$errores[] = "El ancho de la imagen <b>({$this->_ancho})</b> supera el permitido <b>({$this->_anchoPermitido})</b>!";
			//throw new \Exception("El ancho de la imagen <b>({$this->_ancho})</b> supera el permitido <b>({$this->_anchoPermitido})</b>!");
		}
		if ( $this->_ancho < $this->_anchoMinimo ) {
			$errores[] = "El ancho de la imagen <b>({$this->_ancho})</b> es menor al permitido <b>({$this->_anchoMinimo})</b>!";
			//throw new \Exception("El ancho de la imagen <b>({$this->_ancho})</b> supera el permitido <b>({$this->_anchoPermitido})</b>!");
		}
		if ( $this->_alto > $this->_altoPermitido ) {
			$errores[] = "El alto de la imagen <b>({$this->_alto})</b> supera el permitido <b>({$this->_altoPermitido})</b>!";
			//throw new \Exception("El alto de la imagen <b>({$this->_alto})</b> supera el permitido <b>({$this->_altoPermitido})</b>!");
		}
		if ( $this->_alto < $this->_altoMinimo ) {
			$errores[] = "El alto de la imagen <b>({$this->_alto})</b> es menor al permitido <b>({$this->_altoMinimo})</b>!";
			//throw new \Exception("El alto de la imagen <b>({$this->_alto})</b> supera el permitido <b>({$this->_altoPermitido})</b>!");
		}
		if ( $this->_fileSize > $this->_pesoPermitido ) {
			$errores[] = "El peso de la imagen <b>({$this->_fileSize})</b> supera el permitido <b>({$this->_pesoNorma})</b>!";
			//throw new \Exception("El peso de la imagen <b>({$this->_fileSize})</b> supera el permitido <b>({$this->_pesoNorma})</b>!");
		}
		if ( !in_array($this->_ext, $this->_exts) ) {
			$extensiones = implode(", ", $this->_exts);
			$errores[] = "La extensión <b>({$this->_ext})</b> no esta permitida. <b>({$extensiones})</b>!";
			//throw new \Exception("La extensión <b>({$this->_fileType})</b> no esta permitida. <b>({$extensiones})</b>!");
		}
		if ( count($errores) ) {
			$error = implode("<br>", $errores);
			throw new \Exception($error);
		}
		return true;
	}
  
    private function setError()
    {
        $this->_error = array();
        switch ($this->_fileError) {
            case UPLOAD_ERR_OK:
                $this->_error[0] = 0;
                $this->_error[1] = 'UPLOAD_ERR_OK';
                $this->_error[2] = 'NO hay error. El archivo fue subido con éxito';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->_error[0] = 1;
                $this->_error[1] = 'UPLOAD_ERR_FORM_SIZE';
                $this->_error[2] = 'El tamaño limite de la Imagen fue excedido.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->_error[0] = 2;
                $this->_error[1] = 'UPLOAD_ERR_PARTIAL';
                $this->_error[2] = 'La Imagen ha subido parcialmente.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->_error[0] = 3;
                $this->_error[1] = 'UPLOAD_ERR_NO_FILE';
                $this->_error[2] = 'No se ha subido una Imagen.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->_error[0] = 4;
                $this->_error[1] = 'UPLOAD_ERR_NO_TMP_DIR';
                $this->_error[2] = 'Falta el directorio de almacenamiento temporal.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->_error[0] = 5;
                $this->_error[1] = 'UPLOAD_ERR_CANT_WRITE';
                $this->_error[2] = 'No se puede escribir el archivo (posible problema relacionado con los permisos de escritura).';
                break;
            case UPLOAD_ERR_EXTENSION:
                $this->_error[0] = 6;
                $this->_error[1] = 'UPLOAD_ERR_EXTENSION';
                $this->_error[2] = 'Una extensión detuvo la subida del archivo'; 
                break; 
            default:
                $this->_error[0] = 6;
                $this->_error[1] = 'NO CATALOGADO';
                $this->_error[2] = 'Un error en la subida de la Imagen ha ocurrido.';
                break;
        }
        return $this->_error;
    }
	
	public function getError($texto = null)
	{
		if ( $this->_error[0] === 0) {
			return false;
		} else {
			list($n, $e, $s) = $this->_error;
			return $texto ? $s : $e;
		}
	}
	
	public function getExtension()
	{
		return $this->_ext;
	}
	
	public function getNombre()
	{
		return $this->_nombre . '.' . $this->_ext;
	}
}