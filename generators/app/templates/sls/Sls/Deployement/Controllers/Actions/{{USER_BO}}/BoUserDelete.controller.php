<?php
/**
* Class BoUserDelete into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUserDelete extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		// Objects
		$nbDelete = 0;
		
		// Params
		$admins = $this->_http->getParam("id");
		$admins = (SLS_String::contains($admins,"|")) ? explode("|",$admins) : array($admins);
		
		foreach ($admins as $admin)
		{
			$adminExists = $this->_bo->_xmlRight->getTag("//sls_configs/entry[@login='".strtolower($admin)."']/@enabled");
			if (!empty($adminExists))
			{
				$this->_bo->_xmlRight->deleteTags("//sls_configs/entry[@login='".$admin."']");
				$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
				$this->_bo->_xmlRight->refresh();
				$nbDelete++;
			}
		}
		
		if ($this->_bo->_async)
		{
			if ($nbDelete !== false && is_numeric($nbDelete) && $nbDelete > 0)
			{
				$this->_bo->_render["status"] = "OK";
				$this->_bo->_render["result"]["message"] = ($nbDelete==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETES'],$nbDelete);
			}
			echo json_encode($this->_bo->_render);
			die();
		}
		else
		{
			if ($nbDelete !== false && is_numeric($nbDelete) && $nbDelete > 0)
				$this->_bo->pushNotif("success",($nbDelete==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETES'],$nbDelete));
			$this->_generic->forward($this->_bo->_boController, "BoUserList");
		}
		
		$this->saveXML($xml);
	}
}
?>