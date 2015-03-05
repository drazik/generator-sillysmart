<?php
/**
* Class BoSetting into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoSetting extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		// Params
		$settingKey = $this->_http->getParam("Key");
		$settingValue = $this->_http->getParam("Value");
		
		if (SLS_BoRights::isLogged())
		{
			// Set key/value setting
			$settings = $this->_bo->_xmlRight->getTags("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting/@key");
			if (!empty($settings) && in_array($settingKey,$settings))
			{			
				$this->_bo->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='".$settingKey."']", $settingValue);
				$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
				$this->_bo->_xmlRight->refresh();
				$this->_bo->_render["result"] = array_combine($this->_bo->_xmlRight->getTags("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting/@key"),$this->_bo->_xmlRight->getTags("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting"));
			}
			else
				$this->_bo->_render["result"] = array($settingKey => $settingValue);
			$this->_bo->_render["status"] = "OK";
		}
		
		// Render
		echo json_encode($this->_bo->_render);
		die();
	}
}
?>