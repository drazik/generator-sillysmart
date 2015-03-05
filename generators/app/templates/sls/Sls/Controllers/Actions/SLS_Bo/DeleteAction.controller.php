<?php
class SLS_BoDeleteAction extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		$listing = true;
		$errors = array();
		$controller = SLS_String::trimSlashesFromString($this->_http->getParam('Controller'));
		$scontroller = SLS_String::trimSlashesFromString($this->_http->getParam('Action'));
		$controllersXML = $this->_generic->getControllersXML();
		
		if (($controller != 'Home' && $controller != 'Default') || (($controller == 'Home' && $scontroller != 'Index') || ($controller == 'Default' && ($scontroller != 'UrlError' && $scontroller != 'BadRequestError' && $scontroller != 'TemporaryRedirectError' && $scontroller != 'MaintenanceError' && $scontroller != 'AuthorizationError' && $scontroller != 'ForbiddenError' && $scontroller != 'InternalServerError'))))
		{
			$countScontroller = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@name = '".$scontroller."']");
			if (count($countScontroller) == 1)
			{
				// Delete Action Files
				if (is_file($this->_generic->getPathConfig("viewsHeaders").$controller."/".$scontroller.".xsl"))
					unlink($this->_generic->getPathConfig("viewsHeaders").$controller."/".$scontroller.".xsl");
				if (is_file($this->_generic->getPathConfig("viewsBody").$controller."/".$scontroller.".xsl"))
					unlink($this->_generic->getPathConfig("viewsBody").$controller."/".$scontroller.".xsl");
				if (is_file($this->_generic->getPathConfig("actionsControllers").$controller."/".$scontroller.".controller.php"))
					unlink($this->_generic->getPathConfig("actionsControllers").$controller."/".$scontroller.".controller.php");
				foreach ($langs as $lang)
				{
					if (is_file($this->_generic->getPathConfig("actionLangs").$controller."/".$scontroller.".".$lang.".lang.php"))
						unlink($this->_generic->getPathConfig("actionLangs").$controller."/".$scontroller.".".$lang.".lang.php");
				}
				// Delete XML Lines
				$actionID = array_shift($controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@name='".$scontroller."']/@id"));
				$controllersXML->deleteTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@name = '".$scontroller."']");
				file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());
				
				$metasXML = $this->_generic->getCoreXML('metas');
				$metasXML->deleteTags("//sls_configs/action[@id='".$actionID."']");
				file_put_contents($this->_generic->getPathConfig('configSls')."metas.xml", $metasXML->getXML());
				
			}
		}
		
		$this->_generic->forward('SLS_Bo', 'Controllers');	
		$this->saveXML($xml);
	}
	
}
?>