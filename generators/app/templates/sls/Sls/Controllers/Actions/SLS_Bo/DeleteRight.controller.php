<?php
class SLS_BoDeleteRight extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		// Objects		
		$user = $this->hasAuthorative();		
			
		$name = SLS_String::trimSlashesFromString($this->_http->getParam("name"));						
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml");
		$xmlRights = new SLS_XMLToolbox($pathsHandle);			
		$result = $xmlRights->getTags("//sls_configs/entry[@login='".($name)."']");
				
		if (!empty($result))
		{								
			$xmlRights->deleteTags('//sls_configs/entry[@login="'.($name).'"]');
			$xmlRights->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml",$xmlRights->getXML());
		}
		$this->_generic->redirect("Manage/Rights");		
	}
	
}
?>