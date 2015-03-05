<?php
class SLS_BoDeleteForeignKey extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		// Get informations
		$tableFk 	= $this->_http->getParam("tableFk");
		$columnFk 	= $this->_http->getParam("columnFk");
		$tablePk 	= $this->_http->getParam("tablePk");
					
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
		$xmlFk = new SLS_XMLToolbox($pathsHandle);
				
		$xmlFk->saveXML($this->_generic->getPathConfig("configSls")."/fks.xml",$xmlFk->deleteTags("//sls_configs/entry[@tableFk='".$tableFk."' and @columnFk='".$columnFk."' and @tablePk='".$tablePk."']"));
		
		// Force update
		$this->_generic->goDirectTo("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>$tableFk)));		
	}
}