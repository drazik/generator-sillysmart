<?php
/**
* Class BoLogin into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoLogin extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		// Params
		$form = $this->_http->getParam("admin");
		$admin = array_map(array('SLS_String','trimSlashesFromString'),(is_array($form)) ? $form : array());
		$redirect = $this->_http->getParam("Redirect");
		$redirectMore = $this->_http->getParam("RedirectMore");
		$paramsRenew = array("Login" => $admin["login"]);
		if (!empty($redirect))
			$paramsRenew["Redirect"] = $redirect;
		if (!empty($redirectMore))
			$paramsRenew["RedirectMore"] = $redirectMore;
		
		// Already logged
		if (SLS_BoRights::isLogged())				
			$this->_generic->forward($this->_bo->_boController,($this->_bo->_async) ? "BoIsLogged" : "BoDashBoard");		
		
		// Reload
		if (!empty($admin))
		{
			// Login successful
			if (SLS_BoRights::connect($admin["login"],$admin["password"]) == 1)
			{
				$urlForward = $this->_generic->getFullPath($this->_bo->_boController,"BoDashBoard");
				if ($this->_generic->actionIdExists($redirect))
				{
					$mapping = $this->_generic->translateActionId($redirect,$this->_lang->getLang());
					$urlForward = $this->_bo->_boProtocol.'://'.$this->_generic->getSiteConfig('domainName').'/'.$mapping['controller'].'/'.$mapping['scontroller'].((!empty($redirectMore)) ? '/'.str_replace("|","/",$redirectMore) : '').(($this->_generic->getSiteConfig('defaultExtension') != '') ? '.'.$this->_generic->getSiteConfig('defaultExtension') : '');
				}
				$adminColor = $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.(strtolower($admin["login"])).'"]/settings/setting[@key="color"]');
				if (empty($adminColor))
					$adminColor = "pink";
				$xmlBoColors = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo_colors.xml"));
				$adminColorHexa = $xmlBoColors->getTag("//sls_configs/template[@name = '".$adminColor."']/@hexa");
				$this->_bo->_render["status"] = "OK";
				$this->_bo->_render["logged"] = "true";
				$this->_bo->_render["authorized"] = "true";
				$this->_bo->_render["forward"] = $urlForward;
				$this->_bo->_render["result"] = array("login" 		=> $this->_session->getParam("SLS_BO_USER"),
												      "type" 		=> SLS_BoRights::getAdminType(),
												      "name" 		=> ucwords(strtolower($this->_session->getParam("SLS_BO_USER_NAME"))),
												      "firstname" 	=> ucwords(strtolower($this->_session->getParam("SLS_BO_USER_FIRSTNAME"))),
												      "img" 		=> (file_exists($this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg")) ? $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg" : "",
													  "color" 		=> $adminColorHexa);
			}
			// Need renew pwd
			else if (SLS_BoRights::connect($admin["login"],$admin["password"]) == 0)
			{
				$this->_bo->_render["expired"] = "true";
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_LOGIN_ERROR_RENEW'];
				$this->_bo->_render["forward"] = $this->_generic->getFullPath($this->_bo->_boController,"BoRenewPwd", $paramsRenew);
			}
			else if (SLS_BoRights::connect($admin["login"],$admin["password"]) == -2)
			{
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_LOGIN_ERROR_DISABLED'];
			}
			// Login failed
			else
			{
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_LOGIN_ERROR_CREDENTIALS'];
			}
			
			// Async response
			if ($this->_bo->_async)
			{
				echo json_encode($this->_bo->_render);
				die();
			}			
			else
			{
				$xml->startTag("admin");
					$xml->addFullTag("login",$admin["login"],true);
				$xml->endTag("admin");
				
				// All good
				if ($this->_bo->_render["status"] == "OK")
				{					
					if ($this->_generic->actionIdExists($redirect))
					{
						$mapping = $this->_generic->translateActionId($redirect,$this->_lang->getLang());
						$this->_generic->redirect($this->_bo->_boProtocol.'://'.$this->_generic->getSiteConfig('domainName').'/'.$mapping['controller'].'/'.$mapping['scontroller'].((!empty($redirectMore)) ? '/'.str_replace("|","/",$redirectMore) : '').($this->_generic->getSiteConfig('defaultExtension') != '') ? '.'.$this->_generic->getSiteConfig('defaultExtension') : '');
					}
					else
						$this->_generic->forward($this->_bo->_boController,"BoDashBoard");
				}
				// Errors
				else
				{
					// Renew pwd
					if ($this->_bo->_render["expired"] == "true")
					{
						$this->_generic->forward($this->_bo->_boController,"BoRenewPwd", $paramsRenew);
					}
					// Default errors
					else
					{
						$xml->startTag("errors");
						foreach($this->_bo->_render["errors"] as $error)
							$xml->addFullTag("error",$error,true);
						$xml->endTag("errors");
					}
				}
			}
		}
		
		$this->saveXML($xml);
	}
	
	public function after()
	{
		parent::after();
	}
}
?>