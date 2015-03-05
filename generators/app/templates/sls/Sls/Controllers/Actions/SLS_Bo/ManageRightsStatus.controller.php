<?php
/**
* Class ManageRightsStatus into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoManageRightsStatus extends SLS_BoControllerProtected
{
	public function action()
	{
		// Params
		$name = SLS_String::trimSlashesFromString($this->_http->getParam("name"));
		
		// Objects
		$user = $this->hasAuthorative();
		$xmlRights = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml"));
		$result = $xmlRights->getTags("//sls_configs/entry[@login='".($name)."']");
		
		if (!empty($result))
		{
			$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($name).'"]', array("enabled" => ($xmlRights->getTag('//sls_configs/entry[@login="'.($name).'"]/@enabled')=='false') ? 'true' : 'false'));
			$xmlRights->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
		}
		
		$this->_generic->forward("SLS_Bo","ManageRights");
	}
}
?>