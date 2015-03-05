<?php
class SLS_BoDeleteFilter extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		// Get informations
		$table	= $this->_http->getParam("table");
		$column = $this->_http->getParam("column");
		$filter = $this->_http->getParam("filter");
							
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
		$xmlFilter = new SLS_XMLToolbox($pathsHandle);
				
		$xmlFilter->saveXML($this->_generic->getPathConfig("configSls")."/filters.xml",$xmlFilter->deleteTags("//sls_configs/entry[@table='".$table."' and @column='".$column."' and @filter='".$filter."']"));
		
		// Force update
		$this->_generic->goDirectTo("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>$table)));			
	}
}