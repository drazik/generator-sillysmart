<?php
/**
* Class BoIsLogged into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoIsLogged extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		if (SLS_BoRights::isLogged())
		{
			$this->_bo->_render["status"] = "OK";
			$this->_bo->_render["logged"] = "true";
			$this->_bo->_render["authorized"] = "true";
			$this->_bo->_render["result"] = array("login" 		=> $this->_session->getParam("SLS_BO_USER"),
												  "type" 		=> SLS_BoRights::getAdminType(),
												  "name" 		=> ucwords(strtolower($this->_session->getParam("SLS_BO_USER_NAME"))),
												  "firstname" 	=> ucwords(strtolower($this->_session->getParam("SLS_BO_USER_FIRSTNAME"))),
												  "img" 		=> (file_exists($this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg")) ? $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg" : "");
		}
		
		// Response
		echo json_encode($this->_bo->_render);
		die();
	}
	
	public function after()
	{
		parent::after();
	}	
}
?>