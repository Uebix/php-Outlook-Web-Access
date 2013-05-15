<?php 
require_once dirname(__FILE__)."/connection.php";

class Calendar
{
  
	var $server = "";
	var $folder = "";
	var $username = "";
	var $password = "";
	var $appointments;
	var $appointment = array(
		"necessary"=>"",
		"optional"=>"",
		"resources"=>"",
		"subject"=>"",
		"location"=>"",
		"attachment"=>"",
		"dtstart"=>"",
		"dtend"=>"",
		"allday"=>"",
		"reminders"=>"",
		"status"=>"",
		"text"=>""
	);
	
	function __construct($server, $folder, $username = "", $password = "", $calendarFolder = _CALFOLDER_)
	{
		$this->server = (substr($server,-1) == "/" || substr($server,-1) == "\\" ? $server : $server."/");
		$this->folder = $folder.(substr($calendarFolder,-1) == "/" || substr($calendarFolder,-1) == "\\" ? $calendarFolder : $calendarFolder."/");
		$this->username = $username;
		$this->password = $password;
		
	}
	
	function getAppointments($startdate = null,$enddate = null)
	{
		$resultreq = new Connection($this->server.$this->folder,$this->username,$this->password);
		$req = "";
		$where = "";
		if (isset($startdate))
         	$where .= "AND \"urn:schemas:calendar:dtstart\" >= '".$startdate."' ";
        if (isset($startdate))
         	$where .= "AND \"urn:schemas:calendar:dtend\" <= '".$enddate."' ";
		$req = <<<END
<a:searchrequest xmlns:a="DAV:" >
        <a:sql> Select * 
        		FROM Scope('SHALLOW TRAVERSAL OF "{folder}"')
                WHERE NOT "urn:schemas:calendar:instancetype" = 1
                AND "DAV:contentclass" = 'urn:content-classes:appointment'
                {where}
                ORDER BY "urn:schemas:calendar:dtstart" ASC
         </a:sql>
</a:searchrequest>
END;

		$req = str_replace("{folder}","/".$this->folder,$req);
		$req = str_replace("{where}",$where,$req);
/*
		= <<<END
		<a:searchrequest xmlns:a="DAV:" xmlns:cal="urn:schemas:calendar:">
			<a:sql> SELECT *
	                FROM Scope('SHALLOW TRAVERSAL OF "$this->folder"')
	                WHERE NOT "urn:schemas:calendar:instancetype" = 1
	                AND "DAV:contentclass" = 'urn:content-classes:appointment'
	                $req
	                ORDER BY "urn:schemas:calendar:dtstart" ASC
	         </a:sql>
		</a:searchrequest>
END;
			*/	                
/*
 $req = <<<END
<a:searchrequest xmlns:a="DAV:" >
	<a:sql> SELECT *
END;
		$req .= "FROM Scope('SHALLOW TRAVERSAL OF \"$this->folder\"')
            WHERE NOT \"urn:schemas:calendar:instancetype\" = 1
            AND \"DAV:contentclass\" = 'urn:content-classes:appointment' ";
		if (isset($startdate))
			$req .= "AND \"urn:schemas:calendar:dtstart\" >= '".$startdate."' ";
		if (isset($startdate))
			$req .= "AND \"urn:schemas:calendar:dtend\" <= '".$enddate."' ";
		$req .= "ORDER BY \"urn:schemas:calendar:dtstart\" ASC";
		$req .= <<<END
     </a:sql>
</a:searchrequest>
END; 
 */				   

           
				                
		$xml = $resultreq->fetch($req,"SEARCH");
		
		$booFind = false;
		if (isset($xml->A_MULTISTATUS[0]->A_RESPONSE))
			foreach($xml->A_MULTISTATUS[0]->A_RESPONSE as $idx=>$item)
			{
				if (!$booFind)
					$this->appointments = array();
				$booFind = true;
				
				$summaryapp = array(
					"subject"=>"",
					"location"=>"",
					"attachment"=>"",
					"dtstart"=>"",
					"dtend"=>"",
					"allday"=>"",
					"reminders"=>""
				);
				
				$summaryapp["subject"] = $item->A_PROPSTAT[0]->A_PROP[0]->J_SUBJECT[0]->_text;
				$summaryapp["location"] = $item->A_PROPSTAT[0]->A_PROP[0]->D_LOCATION[0]->_text;
				$summaryapp["attachment"] = $item->A_PROPSTAT[0]->A_PROP[0]->E_HASATTACHMENT[0]->_text;
				$summaryapp["dtstart"] = $item->A_PROPSTAT[0]->A_PROP[0]->D_DTSTART[0]->_text;
				$summaryapp["dtend"] = $item->A_PROPSTAT[0]->A_PROP[0]->D_DTEND[0]->_text;
				$summaryapp["allday"] = $item->A_PROPSTAT[0]->A_PROP[0]->D_ALLDAYEVENT[0]->_text;
				$summaryapp["reminders"] = $item->A_PROPSTAT[0]->A_PROP[0]->D_REMINDEROFFSET[0]->_text;
				$this->appointments[$idx] = $summaryapp;
				unset($summaryapp);
				
			}
		if ($booFind)
			return true;
		else
			return false;
	}
	
	function getAppointment()
	{
		
	}
	
	function setAppuntamento()
	{
		
	}
	
}
?>
