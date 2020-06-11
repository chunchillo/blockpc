<?php
/* Funciones.php */
namespace Blockpc\Librerias;

class Funciones {
	
	public function __construct() {}
	
	/*
	 * FUNCION: fecha
	 * Retorna la fecha actual como string legible
	 * @params fecha string en formato fecha
	 * @params delimeter string separador
	 * @return string
	 */
	public function fecha(string $fecha = null, string $delimeter = "-") : string
    {
		/*
		 * Y:	Una representación numérica completa de un año, 4 dígitos
		 * n: 	1 (para Enero) hasta 12 (para Diciembre)
		 * N:  	1 (para lunes) hasta 7 (para domingo)
		 * j: 	Día del mes sin ceros iniciales. 1 al 31
		 */
		if ( !$fecha ) {
			$hoy = date('Y-n-N');
			list($a, $m, $d) = explode('-', $hoy);
			$dia = date('j');
		} else {
			list($a, $m, $dia) = explode($delimeter, $fecha);
			$m = (int) $m;
			$d = date("w", mktime(0, 0, 0, $m, $dia, $a));
			if( $d == 0) $d = 7;
		}
		$dias = array(
            '1' => 'Lunes',
            '2' => 'Martes',
            '3' => 'Miércoles',
            '4' => 'Jueves',
            '5' => 'Viernes',
            '6' => 'Sábado',
            '7' => 'Domingo'
        );
		$meses = array(
            '1' => 'Enero',
            '2' => 'Febrero',
            '3' => 'Marzo',
            '4' => 'Abril',
            '5' => 'Mayo',
            '6' => 'Junio',
            '7' => 'Julio',
            '8' => 'Agosto',
            '9' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        );
		return $dias[$d] . " " . $dia . " de " . $meses[$m] . ", " . $a;
	}
	
	/*
	 * FUNCION: get_client_ip
	 * Retorna la IP del cliiente
	 * @return string
	 */
    public function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
  
    /**
	* FUNCION valida_rut
    * Comprueba si el rut ingresado es valido
    * @param string $rut RUT
    * @return boolean
    */
    public function valida_rut($rut)
    {
        if (!preg_match("/^[0-9.]+[-]?+[0-9kK]{1}/", $rut)) {
            return false;
        }
        $rut = preg_replace('/[\.\-]/i', '', $rut);
        $dv = substr($rut, -1);
        $numero = substr($rut, 0, strlen($rut) - 1);
        $i = 2;
        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i == 8)
                $i = 2;
            $suma += $v * $i;
            ++$i;
        }
        $dvr = 11 - ($suma % 11);
        if ($dvr == 11)
            $dvr = 0;
        if ($dvr == 10)
            $dvr = 'K';
        if ($dvr == strtoupper($dv))
            return true;
        else
            return false;
    }
	
	/*
	 * FUNCION: formatRUT
	 * Retorna RUT de cliente formateado
	 * @return string
	 */
	public function formatRUT(string $rut = ""):string
	{
		// if ( $rut && !$this->valida_rut($rut) ) {
		// 	throw new \Exception("El RUT <b>{$rut}</b> no es un RUT valido!");
		// }
		if ( !$rut ) {
			return "";
		}
		$rut = preg_replace('/[\.\-]/i', '', $rut);
        $dv = substr($rut, -1);
        $numero = substr($rut, 0, strlen($rut) - 1);
		return number_format($numero, 0, ',', '.') . "-" . mb_strtoupper($dv);
	}

	/*
	 * FUNCION: mailTo
	 * Envía Correo desde la aplicación
	 * @param String to_user Nombre Receptor
	 * @param String to_email Correo Receptor
	 * @param String from_user Nombre Remitente
	 * @param String from_email Correo Remitente
	 * @param String asunto Asunto del correo
	 * @param String message Mensaje del correo
	 * @param File archivo Archivo adjunto
	 * @return True False
	 */
    public function mailTo($to_user, $to_email, $from_user, $from_email, $asunto = '(Sin asunto)', $message = '', $archivo = null)
    {
        $from_user = "=?UTF-8?B?".base64_encode($from_user)."?=";
        $asunto = "=?UTF-8?B?".base64_encode($asunto)."?=";
        $cabeceras = "To: {$to_user} <{$to_email}>" . "\r\n";
        $cabeceras .= "From: {$from_user} <{$from_email}>" . "\r\n";
        if ( isset($archivo) ) {
            if(is_file($archivo)) {
                $ext = pathinfo($archivo, PATHINFO_EXTENSION);
                $content = file_get_contents($archivo);
                $content = chunk_split(base64_encode($content));
                // a random hash will be necessary to send mixed content
                $uid = md5(time());
                // carriage return type (RFC)
                $eol = "\r\n";
                
                // main header (multipart mandatory)
                $cabeceras .= "MIME-Version: 1.0" . $eol;
                $cabeceras .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"" . $eol;
                //$cabeceras .= "Content-Transfer-Encoding: 7bit" . $eol;
                //$cabeceras .= "This is a MIME encoded message." . $eol;

                // message
                $mensaje = "--" . $uid . $eol;
                $mensaje .= "Content-Type: text/html; charset=UTF-8" . $eol;
                $mensaje .= "Content-Transfer-Encoding: 8bit" . $eol;
                $mensaje .= $message . $eol;

                // attachment
                $mensaje .= "--" . $uid . $eol;
                $mensaje .= "Content-Type: application/octet-stream; name=\"" . basename($archivo) . "\"" . $eol;
                $mensaje .= "Content-Description: " . basename($archivo) . $eol;
                $mensaje .= "Content-Disposition: attachment; filename=\"" . basename($archivo) . "\"" . $eol;
				$mensaje .= "Content-Transfer-Encoding: base64" . $eol;
                $mensaje .= $content . $eol;
                $mensaje .= "--" . $uid . "--";
            } else {
                throw new \Exception("El archivo adjunto no existe!");
            }     
        } else {
            $mensaje = str_replace("\n.", "\n..", $message);
            $mensaje = wordwrap($mensaje, 128, "\r\n");
            $cabeceras .= "MIME-Version: 1.0" . "\r\n";
            $cabeceras .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        }
        return mail($to_email, $asunto, $mensaje, $cabeceras); 
    }
	
	/*
	 * FUNCION: get_browser_name
	 * Retorna Navegador del Cliente
	 * @return string
	 */
	public function get_browser_name()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
		elseif (strpos($user_agent, 'Edge')) return 'Edge';
		elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
		elseif (strpos($user_agent, 'Safari')) return 'Safari';
		elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
		elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
		
		return 'Other';
    }
    
    /*
	 * FUNCION: toObject
	 * Retorna un objeto
	 * @params array array
	 * @return object
	 */
    public function toObject(array $arreglo = null )
    {
        return json_decode(json_encode($arreglo), FALSE);
    }
	
}