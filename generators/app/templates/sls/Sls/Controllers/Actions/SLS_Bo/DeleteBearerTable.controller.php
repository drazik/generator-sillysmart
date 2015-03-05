<?php
class SLS_BoDeleteBearerTable extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
				
		// Objects		
		$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
		$xmlBearer = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bearers.xml"));
						
		// Get the table & class name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		
		$result = array_shift($xmlBearer->getTagsAttributes("//sls_configs/entry[@tableBearer='".$class."']",array("table1")));
		if (!empty($result))
			$xmlBearer->saveXML($this->_generic->getPathConfig("configSls")."/bearers.xml",$xmlBearer->deleteTags("//sls_configs/entry[@tableBearer='".$class."']"));
		
		$this->_generic->forward("SLS_Bo","EditModel",array(array("key"=>"name","value"=>$this->_http->getParam("name"))));
	}
	
}
?>