<?php
class SLS_BoDeleteType extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		// Get informations
		$table	= $this->_http->getParam("table");
		$column = $this->_http->getParam("column");
							
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
		$xmlType = new SLS_XMLToolbox($pathsHandle);
				
		$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->deleteTags("//sls_configs/entry[@table='".$table."' and @column='".$column."']"));
		
		// Force update
		$this->_generic->goDirectTo("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>$table)));		
	}
}