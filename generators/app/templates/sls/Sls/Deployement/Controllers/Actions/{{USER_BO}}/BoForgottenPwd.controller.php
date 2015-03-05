<?php
/**
* Class BoForgottenPwd into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoForgottenPwd extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		// Params
		$form = $this->_http->getParam("admin");
		$admin = array_map(array('SLS_String','trimSlashesFromString'),(is_array($form)) ? $form : array());
						
		// Already logged
		if (SLS_BoRights::isLogged())
			$this->_generic->forward($this->_bo->_boController,($this->_bo->_async) ? "BoIsLogged" : "BoDashBoard");		
		
		// Reload
		if (!empty($admin))
		{
			$adminInfos = array_shift($this->_bo->_xmlRight->getTagsAttributes("//sls_configs/entry[@login='".strtolower($admin["login"])."']",array("password","name","firstname")));
			
			if (empty($adminInfos))
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_FORGOTTEN_ERROR_UNKNOWN_ACCOUNT'];
			else if (!SLS_String::validateEmail($admin["login"]))
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_FORGOTTEN_ERROR_UNKNOWN_EMAIL'];
			else
			{
				$token = substr($adminInfos["attributes"][0]["value"],12,6).substr(sha1($adminInfos["attributes"][1]["value"]),24,2).substr(sha1($adminInfos["attributes"][2]["value"]),8,2);
				
				$urlReset = $this->_generic->getFullPath($this->_bo->_boController,"BoRenewPwd",array("Login" => $admin["login"], "Token" => $token));
				$email = new SLS_Email();
				$email->addRecipient($admin["login"], "To");
				$email->setSubject("[".$this->_generic->getSiteConfig("projectName")."] ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_FORGOTTEN_SUCCESS_EMAIL_SUBJECT']);
				$email->setHtml(sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_FORGOTTEN_SUCCESS_EMAIL_CONTENT'],$urlReset,$urlReset,$this->_generic->getSiteConfig("projectName")));
				$email->send();
				
				$this->_bo->_render["status"] = "OK";
				$this->_bo->_render["result"]["success"] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_FORGOTTEN_SUCCESS_MESSAGE'];
			}
			
			// Async response
			if ($this->_bo->_async)
			{
				$this->_bo->_render["forward"] = $this->_generic->getFullPath($this->_bo->_boController,"BoLogin");
				echo json_encode($this->_bo->_render);
				die();
			}			
			else
			{
				// All good
				if ($this->_bo->_render["status"] == "OK")
				{	
					$xml->addFullTag("success",$this->_bo->_render["result"]["success"],true);
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
		
		$this->saveXML($xml);
	}
}
?>