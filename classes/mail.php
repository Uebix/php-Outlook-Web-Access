<?php 
require_once dirname(__FILE__)."/connection.php";

class Email
{
  
	var $server = "";
	var $folder = "";
	var $username = "";
	var $password = "";
	var $emails;
	var $numemails = 0;
	
	function __construct($server, $folder, $username = "", $password = "", $mailFolder = _MAILFOLDER_)
	{
		$this->server = (substr($server,-1) == "/" || substr($server,-1) == "\\" ? $server : $server."/");
		$this->folder = $folder.(substr($mailFolder,-1) == "/" || substr($mailFolder,-1) == "\\" ? $mailFolder : $mailFolder."/");
		$this->username = $username;
		$this->password = $password;
		
	}
	
	function numEmails($refresh = false)
	{
		if ($refresh)
		{
			$resultreq = new Connection($this->server.$this->folder,$this->username,$this->password);
			$req = "";
			$where = "";
			
			$req = <<<END
<a:searchrequest xmlns:a="DAV:" >
   <a:sql>
       SELECT "DAV:displayname"
       FROM "{folder}"
       WHERE "DAV:ishidden" = FALSE AND "DAV:isfolder" = FALSE
   </a:sql>
</a:searchrequest>
END;

			$req = str_replace("{folder}","/".$this->folder,$req);
			$req = str_replace("{where}",$where,$req);
					                
			$xml = $resultreq->fetch($req,"SEARCH");
			$this->numemails = count($xml->A_MULTISTATUS[0]->A_RESPONSE);
		} 
		return $this->numemails;
	}
	
	function getMails($number = null, $where = "")
	{
		if(is_numeric($number))
			if($number > 0)
			{
				$header = array("Range" => "rows=0-".($number - 1));
			}elseif($number < 0)
				{
					$header = array("Range" => "rows=".($number));
				}
		
		$resultreq = new Connection($this->server.$this->folder,$this->username,$this->password,$header);
		$req = <<<END
<a:searchrequest xmlns:a="DAV:" >
   <a:sql>
       SELECT *
       FROM "{folder}"
       WHERE "DAV:ishidden" = FALSE AND "DAV:isfolder" = FALSE
       ORDER BY "urn:schemas:httpmail:date" DESC
   </a:sql>
</a:searchrequest> 
END;

		$req = str_replace("{folder}","/".$this->folder,$req);
		$req = str_replace("{where}",$where,$req);
				                
		$xml = $resultreq->fetch($req,"SEARCH");
		
		//echo $req;
		/*echo "<pre>\n";
		print_r($xml);
		echo "</pre>\n";
		exit;*/
		$booFind = false;
		
		if (isset($xml->A_MULTISTATUS[0]->A_RESPONSE))
			foreach($xml->A_MULTISTATUS[0]->A_RESPONSE as $idx=>$item)
			{
				if (!$booFind)
					$this->emails = array();
				$booFind = true;
				$summarymail = array(
					"href"=>"",
					"id"=>"",
					"subject"=>"",
					"fromname"=>"",
					"fromemail"=>"",
					"attachment"=>"",
					"read"=>"",
					"date"=>""
				);
				$item = $item->A_PROPSTAT[0]->A_PROP[0];
				$date = $item->D_DATE[0]->_text;
				
				$summarymail["href"] = urlencode(basename($item->A_HREF[0]->_text));
				$summarymail["id"] = $item->D_MESSAGE_ID[0]->_text;
				$summarymail["subject"] = $item->E_SUBJECT[0]->_text;
				$summarymail["fromname"] = $item->E_FROMNAME[0]->_text;
				$summarymail["fromemail"] = $item->E_FROMEMAIL[0]->_text;
				$summarymail["attachment"] = $item->E_HASATTACHMENT[0]->_text;
				$summarymail["read"] = $item->E_READ[0]->_text;
				$summarymail["date"] = substr($date,8,2).'-'.substr($date,5,2).'-'.substr($date,0,4);
				
				$this->emails[$idx] = $summarymail;
				unset($summarymail);
			}
		if ($booFind)
			return true;
		else
			return false;
	}
	
	function getMail($href)
	{
		$resultreq = new Connection($this->server.$this->folder.$href,$this->username,$this->password);
		$req = <<<END
			<a:propfind xmlns:a="DAV:">
			    <a:allprop/>
			</a:propfind> 
END;

		$xml = $resultreq->fetch($req,"PROPFIND");
		
		//echo $req;
		/*echo "<pre>\n";
		print_r($xml);
		echo "</pre>\n";
		exit;*/
		$email = false;
		
		if (isset($xml->A_MULTISTATUS[0]->A_RESPONSE[0]->A_PROPSTAT[0]->A_PROP[0]))
		{
			$email = array(
				"subject"=>"",
				"href"=>"",
				"id"=>"",
				"fromname"=>"",
				"fromemail"=>"",
				"toname"=>"",
				"ccname"=>"",
				"tomail"=>"",
				"ccmail"=>"",
				"htmlbody"=>"",
				"textbody"=>"",
				"readonly"=>"0",
				"attachment"=>"0",
				"read"=>"0",
				"date"=>"",
				"reminders"=>"",
				"status"=>"",
				"text"=>""
			);
			$item = $xml->A_MULTISTATUS[0]->A_RESPONSE[0]->A_PROPSTAT[0]->A_PROP[0];
			$date = $item->D_DATE[0]->_text;
			
			$email["subject"] = (isset($item->E_SUBJECT[0]->_text) ? $item->E_SUBJECT[0]->_text : "");
			$email["href"] = (isset($item->A_HREF[0]->_text) ? urlencode(basename($item->A_HREF[0]->_text)) : "");
			$email["id"] = (isset($item->D_MESSAGE_ID[0]->_text) ? $item->D_MESSAGE_ID[0]->_text : "");
			$email["fromname"] = (isset($item->E_FROMNAME[0]->_text) ? $item->E_FROMNAME[0]->_text : "");
			$email["fromemail"] = (isset($item->E_FROMEMAIL[0]->_text) ? $item->E_FROMEMAIL[0]->_text : "");
			$email["toname"] = (isset($item->E_DISPLAYTO[0]->_text) ? $item->E_DISPLAYTO[0]->_text : "");
			$email["tomail"] = (isset($item->E_TO[0]->_text) ? $item->E_TO[0]->_text : "");
			$email["ccname"] = (isset($item->E_DISPLAYCC[0]->_text) ? $item->E_DISPLAYCC[0]->_text : "");
			$email["textbody"] = (isset($item->E_TEXTDESCRIPTION[0]->_text) ? $item->E_TEXTDESCRIPTION[0]->_text : "");
			$email["htmlbody"] = (isset($item->E_HTMLDESCRIPTION[0]->_text) ? $item->E_HTMLDESCRIPTION[0]->_text : $email["textbody"]);
			$email["readonly"] = ($item->A_ISREADONLY[0]->_text == 1 ? 1 : 0);
			$email["read"] = ($item->E_READ[0]->_text == 1 ? 1 : 0);
			$email["date"] = substr($date,8,2).'-'.substr($date,5,2).'-'.substr($date,0,4).' '.substr($date,11,2).':'.substr($date,14,2).':'.substr($date,17,2);
			//$email["cc"] = $item->E_CC[0]->_text;
			$email["ccmail"] = (isset($item->E_CC[0]->_text) ? $item->E_CC[0]->_text : "");
			$email["attachment"] = ($item->E_HASATTACHMENT[0]->_text == 1 ? $this->getAttachments($href) : 0 );
		}
		return $email;
	}
	
	function getAttachments($href)
	{
		$resultreq = new Connection($this->server.$this->folder.$href,$this->username,$this->password);
		$xml = $resultreq->fetch("","X-MS-ENUMATTS");
	}
	
	function setMail()
	{
		
	}
	
}
?>
