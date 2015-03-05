<?php
/**
* Class BoRenewPwd into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoRenewPwd extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		// Params
		$login = strtolower($this->_http->getParam("Login"));
		$token = $this->_http->getParam("Token");
		$redirect = $this->_http->getParam("Redirect");
		$redirectMore = $this->_http->getParam("RedirectMore");
		$paramsRenew = array();
		if (!empty($redirect))
			$paramsRenew["Redirect"] = $redirect;
		if (!empty($redirectMore))
			$paramsRenew["RedirectMore"] = $redirectMore;
		$attributes = array_shift($this->_bo->_xmlRight->getTagsAttributes("//sls_configs/entry[@login='".($login)."']",array("password","complexity_pwd","min_chars_pwd","renew_pwd_nb","password_old","reset_pwd","name","firstname")));
		$needPassword = true;
		
		// Unknwown admin
		if (empty($attributes))
			$this->_generic->forward($this->_bo->_boController,"BoLogin");
		// Lost password ?
		if (!empty($token))
		{
			$realToken = substr($attributes["attributes"][0]["value"],12,6).substr(sha1($attributes["attributes"][6]["value"]),24,2).substr(sha1($attributes["attributes"][7]["value"]),8,2);
			if ($token == $realToken)
			{
				$needPassword = false;
				$current_password = $attributes["attributes"][0]["value"];
			}
		}
		
		// Reload
		if ($this->_http->getParam("reload-renew") == "true")
		{			
			$complexity = explode("|",$attributes["attributes"][1]["value"]);
			$old_passwords = explode("|",$attributes["attributes"][4]["value"]);

			$form 				= $this->_http->getParam("admin");
			$admin 				= array_map(array('SLS_String','trimSlashesFromString'),(is_array($form)) ? $form : array());
			$new_password 		= $admin["new_password"];
			$new_password2 		= $admin["new_password2"];
			if ($needPassword)
				$current_password 	= sha1($admin["current_password"]);
				
			if ($needPassword && $current_password != $attributes["attributes"][0]["value"])
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_CURRENT_PASSWORD'];
			else
			{
				if (empty($new_password))
					$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_EMPTY'];
				else if ($new_password != $new_password2)
					$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_BOTH'];
				else if ($current_password == sha1($new_password))
					$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_SAME_CURRENT'];
				else
				{
					if (strlen($new_password) < $attributes["attributes"][2]["value"])
						$this->_bo->_render["errors"][] = sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_MIN_CHARACTERS'], $attributes["attributes"][2]["value"]);
					if (in_array("lc",$complexity) && preg_match('`[[:lower:]]`', $new_password) === 0)
						$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_LC'];
					if (in_array("uc",$complexity) && preg_match('`[[:upper:]]`', $new_password) === 0)
						$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_UC'];
					if (in_array("digit",$complexity) && preg_match('`[[:digit:]]`', $new_password) === 0)
						$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_DIGIT'];
					if (in_array("special_char",$complexity) && preg_match('`[^a-zA-Z0-9]`', $new_password) === 0)
						$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_SPECIAL_CHAR'];
					if (in_array(sha1($new_password),$old_passwords))
						$this->_bo->_render["errors"][] = sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_ERROR_NEW_PASSWORD_SAME_OLDS'], $attributes["attributes"][3]["value"]);
				}
			}
			
			if (empty($this->_bo->_render["errors"]))
			{
				// Save new pwd
				if ($attributes["attributes"][5]["value"] == "true")
					$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$login."']",array("reset_pwd" => ""));
				$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$login."']",array("password" => sha1($new_password)));
				$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$login."']",array("last_renew_pwd" => date("Y-m-d")));
				if (count($old_passwords) < $attributes["attributes"][3]["value"])
				{
					array_unshift($old_passwords,$current_password);
					$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$login."']",array("password_old" => implode("|",$old_passwords)));
				}
				else
				{
					array_pop($old_passwords);
					array_unshift($old_passwords,$current_password);
					$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$login."']",array("password_old" => implode("|",$old_passwords)));
				}
				$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
				$this->_bo->_xmlRight->refresh();

				// Force login
				SLS_BoRights::connect($login,$new_password);
				
				// Forward
				$urlForward = $this->_generic->getFullPath($this->_bo->_boController,"BoDashBoard");
				if ($this->_generic->actionIdExists($redirect))
				{
					$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CHANGE_PWD']);
					$mapping = $this->_generic->translateActionId($redirect,$this->_lang->getLang());
					$urlForward = $this->_bo->_boProtocol.'://'.$this->_generic->getSiteConfig('domainName').'/'.$mapping['controller'].'/'.$mapping['scontroller'].((!empty($redirectMore)) ? '/'.str_replace("|","/",$redirectMore) : '').(($this->_generic->getSiteConfig('defaultExtension') != '') ? '.'.$this->_generic->getSiteConfig('defaultExtension') : '');
				}
				
				// Async response
				if ($this->_bo->_async)
				{
					$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CHANGE_PWD']);
					$this->_bo->_render["status"] = "OK";
					$this->_bo->_render["logged"] = "true";
					$this->_bo->_render["authorized"] = "true";
					$this->_bo->_render["expired"] = "false";
					$this->_bo->_render["forward"] = $urlForward;
					$this->_bo->_render["result"] = array("message" 	=> $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CHANGE_PWD'],
														  "login" 		=> $this->_session->getParam("SLS_BO_USER"),
													      "type" 		=> SLS_BoRights::getAdminType(),
													      "name" 		=> ucwords(strtolower($this->_session->getParam("SLS_BO_USER_NAME"))),
													      "firstname" 	=> ucwords(strtolower($this->_session->getParam("SLS_BO_USER_FIRSTNAME"))),
												     	  "img" 		=> (file_exists($this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg")) ? $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($this->_session->getParam("SLS_BO_USER_NAME")."_".$this->_session->getParam("SLS_BO_USER_FIRSTNAME"),"_").".jpg" : "");
					
					echo json_encode($this->_bo->_render);
					die();
				}
				// Default redirect
				else
				{
					$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CHANGE_PWD']);
					$this->_generic->redirect($urlForward);
				}
			}
			else
			{
				// Async response
				if ($this->_bo->_async)
				{					
					echo json_encode($this->_bo->_render);
					die();
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
		
		$attributes = array_shift($this->_bo->_xmlRight->getTagsAttributes("//sls_configs/entry[@login='".($login)."']",array("complexity_pwd","min_chars_pwd","renew_pwd_nb")));
		$complexity_collection = explode("|",$attributes["attributes"][0]["value"]);
		$complexity_litteral = "";
		for($i=0 ; $i<$count=count($complexity_collection) ; $i++)
			$complexity_litteral .= $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_RENEW_PASSWORD_COMPLEXITY_CHARS_'.strtoupper($complexity_collection[$i])].(($i<($count-1)) ? ", " : "");
		$xml->startTag("infos");
			$xml->addFullTag("complexity_pwd",str_replace("|",", ",$attributes["attributes"][0]["value"]),true);
			$xml->addFullTag("complexity_pwd_litteral",$complexity_litteral,true);
			$xml->addFullTag("min_chars_pwd",$attributes["attributes"][1]["value"],true);
			$xml->addFullTag("renew_pwd_nb",$attributes["attributes"][2]["value"],true);
			$xml->addFullTag("need_pwd",($needPassword) ? "true" : "false",true);
		$xml->endTag("infos");

		$this->saveXML($xml);
	}
	
	public function after()
	{
		parent::after();
	}
}
?>