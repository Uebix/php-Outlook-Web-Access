<?php
require_once dirname(__FILE__)."/class_http.php";
require_once dirname(__FILE__)."/class_xml.php";

class Connection
{
  var $address = "";
	var $username = "";
	var $password = "";
	var $http;
	
	function __construct($address, $username = "", $password = "", $headers = null)
	{
		$this->address = (substr($address,-1) == "/" || substr($address,-1) == "\\" ? $address : $address."/");
		$this->username = $username;
		$this->password = $password;
		
		$this->http = new http();
		$this->http->headers["Content-Type"] = 'text/xml; charset="UTF-8"';
		$this->http->headers["Depth"] = "0";
		$this->http->headers["Translate"] = "f";
		if (isset($headers))
		{
			$this->http->headers = array_merge($this->http->headers,$headers);
		}
		
		$this->http->xmlrequest = '<?xml version="1.0"?>';
	}
	
	function fetch($request, $reqtype)
	{	
		if($reqtype == "X-MS-ENUMATTS")
			$this->http->xmlrequest = "";
		else
			$this->http->xmlrequest .= $request;
			
		if (!$this->http->fetch($this->address, 0, null, $this->username, $this->password, $reqtype)) {
			if (_DEBUG_)
			{
	  			echo "<h2>There is a problem with the http request!</h2>";
	  			echo $this->http->log;
	  			exit();
			}
		}
		$xml = new xml();
		if (!$xml->fetch($this->http->body)) {
			if (_DEBUG_)
			{
			    echo "<h2>There was a problem parsing your XML!</h2>";
			    echo "<pre>".$this->http->log."</pre><hr />\n";
			    echo "<pre>".$this->http->header."</pre><hr />\n";
			    echo "<pre>".$this->http->body."</pre><hr />\n";
			    echo "<pre>".$xml->log."</pre><hr />\n";
			    echo "<pre>".$this->http->xmlrequest."</pre>";
			    exit();
			}
		}
		return $xml->data;
	}
	
}
?>
