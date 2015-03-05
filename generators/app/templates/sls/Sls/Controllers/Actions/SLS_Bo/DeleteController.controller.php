<?php
class SLS_BoDeleteController extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		$listing = true;
		$errors = array();
		$controllersXML = $this->_generic->getControllersXML();
		$controller = SLS_String::trimSlashesFromString($this->_http->getParam('Controller'));
		
		if ($controller != 'Home' && $controller != 'Default')
		{
		// We want to delete the controller
		
			$controllers = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']");
			if (count($controllers) == 1)
			{
				$controllerId = array_shift($controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/@id"));
				// Delete all files
				// Views Header
				(is_dir($this->_generic->getPathConfig("viewsHeaders").$controller)) ? $this->_generic->rm_recursive($this->_generic->getPathConfig("viewsHeaders").$controller) : SLS_Tracing::addTrace(new Exception("Directory ".$this->_generic->getPathConfig("viewsHeaders").$controller." cannot be removed"));
				// Views Body
				(is_dir($this->_generic->getPathConfig("viewsBody").$controller)) ? $this->_generic->rm_recursive($this->_generic->getPathConfig("viewsBody").$controller) : SLS_Tracing::addTrace(new Exception("Directory ".$this->_generic->getPathConfig("viewsBody").$controller." cannot be removed"));
				// Langs
				(is_dir($this->_generic->getPathConfig("actionLangs").$controller)) ? $this->_generic->rm_recursive($this->_generic->getPathConfig("actionLangs").$controller) : SLS_Tracing::addTrace(new Exception("Directory ".$this->_generic->getPathConfig("actionLangs").$controller." cannot be removed"));
				// Delete controller Directory
				(is_dir($this->_generic->getPathConfig("actionsControllers").$controller)) ? $this->_generic->rm_recursive($this->_generic->getPathConfig("actionsControllers").$controller) : SLS_Tracing::addTrace(new Exception("Directory ".$this->_generic->getPathConfig("actionsControllers").$controller." cannot be removed"));
				// Delete XML Informations
				$controllersXML->deleteTags("//controllers/controller[@side='user' and @name='".$controller."']");
				file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());
				$metasXML = $this->_generic->getCoreXML('metas');
				$metasXML->deleteTags("//sls_configs/action[@id='".$controllerId."']");
				file_put_contents($this->_generic->getPathConfig('configSls')."metas.xml", $metasXML->getXML());
			}
		}
		$this->_generic->forward('SLS_Bo', 'Controllers');
		$this->saveXML($xml);
	}
	
}
?>