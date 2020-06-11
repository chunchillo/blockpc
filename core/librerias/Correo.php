<?php
/* Correo.php */
namespace Blockpc\Librerias;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Correo {
	
	private $_mail;
	
	public function __construct() {
		$this->_mail = new PHPMailer(true);
		//Server settings
		//$this->_mail->SMTPDebug  = 2;                          	// Enable verbose debug output
		$this->_mail->CharSet 	 = 'UTF-8';
		$this->_mail->isSMTP();                                	// Set mailer to use SMTP
		$this->_mail->Host       = 'mail.controlando.cl';  	// Specify main and backup SMTP servers
		$this->_mail->SMTPAuth   = true;                       	// Enable SMTP authentication
		$this->_mail->Username   = 'soporte@controlando.cl';  	// SMTP username
		$this->_mail->Password   = 'KiB@CuL(LsM)}';          			// SMTP password
		$this->_mail->SMTPSecure = 'tls';                      	// Enable TLS encryption, `ssl` also accepted
		$this->_mail->Port       = 587;                        	// TCP port to connect to (587)
	}
	
	public function setFrom(string $mail, string $name = null)
	{
		if ( $name ) {
			$this->_mail->setFrom($mail, $name);
		} else {
			$this->_mail->setFrom($mail);
		}
	}
	
	public function addCC(string $mail, string $name = null)
	{
		if ( $name ) {
			$this->_mail->addCC($mail, $name);
		} else {
			$this->_mail->addCC($mail);
		}
	}
	
	public function addAddress(string $mail, string $name = null)
	{
		if ( $name ) {
			$this->_mail->addAddress($mail, $name);
		} else {
			$this->_mail->addAddress($mail);
		}
	}
	
	public function addAttachment(string $filename = null)
	{
		if ( $filename ) {
			$this->_mail->addAttachment($filename);
		}
	}
	
	public function content(string $subject, string $body, string $altBody = null)
	{
		$this->_mail->isHTML(true); // Set email format to HTML
		$this->_mail->Subject = $subject;
		$this->_mail->Body    = $body;
		if ( $altBody ) {
			$this->_mail->AltBody = $altBody;
		}
	}
	
	public function AddEmbeddedImage(string $image, string $name = null)
	{
		if ( !$name ) {
			$name = basename($image);
		}
		$this->_mail->AddEmbeddedImage($image, "imagen", $name);
	}
	
	public function send()
	{
		try {
			return $this->_mail->send();
		} catch(Exception $e) {
			throw $e->getMessage();
		}
	}
}