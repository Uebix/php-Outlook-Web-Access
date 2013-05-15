<?php

// Modify the paths to these class files as needed.
require_once dirname(__FILE__)."/exchange_config.php";
require_once dirname(__FILE__)."/classes/calendar.php";
require_once dirname(__FILE__)."/classes/email.php";

class Exchange
{
  var $server = "";
	var $username = "";
	var $password = "";
	var $folder = "";
	var $exchfolder = "";
	var $calendar;
	
	function __construct($server, $email = "", $username = "", $password = "",$exchfolder = _EXCHFOLDER_)
	{
		//inserisco se manca lo slash alla fine dell'alias
		$this->server = (substr($server,-1) == "/" || substr($server,-1) == "\\" ? $server : $server."/");
		//echo "SERVER:<pre>".$this->server."</pre>";
		$this->exchfolder = $exchfolder;
		$this->username = $username;
		$this->password = $password;
		
		//prendo l'alias dell'email come directory dell'utente su exchange
		$this->folder = substr($email,0,stripos($email,"@"));
		//inserisco se manca lo slash alla fine dell'alias
		$this->folder = (substr($this->folder,-1) == "/" || substr($this->folder,-1) == "\\" ? $this->folder : $this->folder."/");
		//costruisco l'indirizzo base per tutte le cartelle formato da cartella_exchange_sul_server/nome_cartella_utente
		$this->folder = $this->exchfolder . $this->folder;
		//echo "FOLDER:<pre>".$this->folder."</pre>";
		
		$this->calendar = new Calendar($this->server,$this->folder,$this->username,$this->password);
		$this->email = new Email($this->server,$this->folder,$this->username,$this->password);
		
	}
	
}


?>
