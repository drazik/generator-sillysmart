<?php
class SLS_BoDeletePlugin extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);		
		
		$pluginID = $this->_http->getParam("Plugin");
		$way = $this->_http->getParam("Way");
		if ($way == "Maj")
			$server = $this->_http->getParam("Server");
		
	
		$plugins = $this->_generic->getPluginXML("plugins");
		if (count($plugins->getTags("//plugins/plugin[@id='".$pluginID."']")) > 0)
		{
			
			$path = array_shift($plugins->getTags("//plugins/plugin[@id='".$pluginID."']/@path"));
			$type = array_shift($plugins->getTags("//plugins/plugin[@id='".$pluginID."']/@file"));
			$code = array_shift($plugins->getTags("//plugins/plugin[@id='".$pluginID."']/@code"));
			// Delete Sources Files
			if ($type == 1 && is_file($this->_generic->getPathConfig('plugins').$path))
				unlink($this->_generic->getPathConfig('plugins').$path);
			else if ($type == 0 && is_dir($this->_generic->getPathConfig('plugins').$path))
				$this->_generic->rm_recursive($this->_generic->getPathConfig('plugins').$path);
				
			// Delete Config File
			if (is_file($this->_generic->getPathConfig("configPlugins").$pluginID."_".$code.".xml"))
				unlink($this->_generic->getPathConfig("configPlugins").$pluginID."_".$code.".xml");
				
			$plugins->deleteTags("//plugins/plugin[@id='".$pluginID."']");
			file_put_contents($this->_generic->getPathConfig("configPlugins")."plugins.xml", $plugins->getXML());
		}
		if ($way == "Maj")
		{
			
			$this->goDirectTo("SLS_Bo", "SearchPlugin", array(
														0 => array("key"=>"Way","value"=>"Maj"),
														1 => array("key"=>"Server","value"=>$server),
														2 => array("key"=>"Plugin","value"=>$pluginID),
														3 => array("key"=>"Action","value"=>"Download"),
														));
			
		}

		$this->goDirectTo("SLS_Bo", "Plugins");
	}
	
}
?>