<?php
class SLS_BoDeleteComponentController extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);

		$controller = SLS_String::trimSlashesFromString($this->_http->getParam('Controller'));
		if (is_file($this->_generic->getPathConfig("componentsControllers").$controller.".controller.php"))
		{
			unlink($this->_generic->getPathConfig("componentsControllers").$controller.".controller.php");
		}
		$this->_generic->dispatch('SLS_Bo', 'Controllers');
		$this->saveXML($xml);
	}
	
}
?>