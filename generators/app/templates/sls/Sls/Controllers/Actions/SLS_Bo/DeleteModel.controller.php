<?php
class SLS_BoDeleteModel extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$actions = array("list","add","modify","delete","clone","email");
				
		// Get the table & class name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		$file  = ucfirst($db).".".SLS_String::tableToClass($table);
		
		$sql = SLS_Sql::getInstance();
		
		// If current db is not this one
		if ($sql->getCurrentDb() != $db)
			$sql->changeDb($db);
		
		// If the table exists, delete the bo & model
		if ($sql->tableExists($table))
		{
			foreach($actions as $action)
				if($this->boActionExist($table,$db,$action))
					$this->deleteActionBo($table,$action,$db);
			
			@unlink($this->_generic->getPathConfig("models").$file.".model.php");
			@unlink($this->_generic->getPathConfig("modelsSql").$file.".sql.php");			
		}
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","Models");
		$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller']);
	}
	
}
?>