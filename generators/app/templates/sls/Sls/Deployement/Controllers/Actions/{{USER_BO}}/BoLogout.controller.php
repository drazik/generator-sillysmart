<?php
/**
* Class BoLogout into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoLogout extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		if (SLS_BoRights::isLogged())
			SLS_BoRights::disconnect();
			
		if ($this->_bo->_async)
		{
			$this->_bo->_render["logged"] = "false";
			$this->_bo->_render["expired"] = "false";
			$this->_bo->_render["authorized"] = "false";
			$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_LOGIN_ERROR_AUTHENTICATED'];
			echo $this->_bo->_render;
			die();
		}
		else
			$this->_generic->forward($this->_bo->_boController,"BoLogin");
	}
}
?>