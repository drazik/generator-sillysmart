<?php
/**
* Class BoUserStatus into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUserStatus extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		// Params
		$values = array("true","false");
		$admin = $this->_http->getParam("id");
		$enabled = (in_array($this->_http->getParam("enabled"),$values)) ? $this->_http->getParam("enabled") : array_shift($values);
		
		$adminExists = $this->_bo->_xmlRight->getTag("//sls_configs/entry[@login='".$admin."']/@enabled");
		if (!empty($adminExists))
		{
			$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$admin."']",array("enabled" => $enabled));
			$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
			$this->_bo->_xmlRight->refresh();
			$this->_bo->_render["status"] = "OK";
			$this->_bo->_render["result"] = array("admin" 	=> $admin,
												  "enabled" => ($enabled == "true") ? true : false);
		}
		
		if ($this->_bo->_async)
		{
			echo json_encode($this->_bo->_render);
			die();
		}
		else
		{
			if (!empty($adminExists))
				$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_EDIT']);
			$this->_generic->forward($this->_bo->_boController, "BoUserList");
		}
		
		$this->saveXML($xml);
	}
}
?>