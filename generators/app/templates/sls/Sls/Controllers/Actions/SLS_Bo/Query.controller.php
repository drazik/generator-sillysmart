<?php
class SLS_BoQuery extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$sql = SLS_Sql::getInstance();
		$dbs = $sql->getDbs();
		$results = array();
		$error = "";
		$success = "";
		
		$xml->startTag("dbs");
		foreach($dbs as $db)
			$xml->addFullTag("db",$db,true);
		$xml->endTag("dbs");		
		
		if ($this->_http->getParam("reload") == "true")
		{
			$db = $this->_http->getParam("db");
			$query = trim(SLS_String::trimSlashesFromString($this->_http->getParam("query")));
			
			$sql->changeDb($db);
			
			switch($query)
			{
				case (SLS_String::startsWith(strtolower($query),"select") || 
					  SLS_String::startsWith(strtolower($query),"show") ||
					  SLS_String::startsWith(strtolower($query),"explain") ||
					  SLS_String::startsWith(strtolower($query),"describe")):					
					$results = $sql->select($query);
					if ($results !== false)
						$success = count($results)." row(s) selected";
					break;
				case (SLS_String::startsWith(strtolower($query),"insert")):
					$results = $sql->exec($query);
					if ($results !== false)
						$success = $results." row(s) inserted";
					break;
				case (SLS_String::startsWith(strtolower($query),"update")):
					$results = $sql->update($query);
					if ($results !== false)
						$success = $results." row(s) updated";
					break;
				case (SLS_String::startsWith(strtolower($query),"delete")):
					$results = $sql->delete($query);
					if ($results !== false)
						$success = $results." row(s) deleted";
					break;
				default:
					$results = $sql->query($query);
					if ($results !== false)
						$success = $results;
					break;
			}
			
			if (!empty($success))
				$xml->addFullTag("success",$success,true);
			if ($results === false)
				$xml->addFullTag("error","SQL syntax error",true);
			else if (!empty($results) && is_array($results))
			{
				$xml->startTag("legends");
				foreach($results[0] as $key => $value)
					$xml->addFullTag("legend",strtolower($key),true);
				$xml->endTag("legends");
				
				$xml->startTag("results");
				for($i=0 ; $i<$count=count($results) ; $i++)
				{
					$xml->startTag("result");
					foreach($results[$i] as $key => $value)
						$xml->addFullTag(strtolower($key),$value,true);
					$xml->endTag("result");
				}
				$xml->endTag("results");
			}
			
			$xml->addFullTag("query",$query,true);
		}
		
		$this->saveXML($xml);
	}
	
}
?>