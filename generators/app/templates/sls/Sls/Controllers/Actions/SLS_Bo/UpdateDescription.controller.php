<?php
class SLS_BoUpdateDescription extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		$table 		= SLS_String::substrAfterFirstDelimiter(SLS_String::trimSlashesFromString($this->_http->getParam("__table")),"_");
		$db			= SLS_String::substrBeforeFirstDelimiter(SLS_String::trimSlashesFromString($this->_http->getParam("__table")),"_");
		$columns 	= $this->_http->getParams();
		$class		= ucfirst($db)."_".SLS_String::tableToClass($table);
		$desc		= SLS_String::trimSlashesFromString($this->_http->getParam("description"));
		
		$this->_generic->useModel($table,$db,"user");
		$object = new $class();
		if (!empty($desc))
			$object->setTableComment($desc,$table,$db);
		
		// Descriptions
		foreach($columns as $key => $value)
		{
			if (SLS_String::startsWith($key,"col_"))
			{
				$column = SLS_String::substrAfterFirstDelimiter($key,"_");
				$object->setColumnComment($column,SLS_String::trimSlashesFromString($value),$table);
			}
		}
		
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","EditModel");
		$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"]."/name/".$db."_".$table);
		
		$this->saveXML($xml);
	}
	
}
?>