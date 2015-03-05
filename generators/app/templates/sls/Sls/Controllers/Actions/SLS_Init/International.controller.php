<?php
class SLS_InitInternational extends SLS_InitControllerProtected 
{		
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$this->secureURL();
		$this->_generic->registerLink('International', 'SLS_Init', 'International');
		$errors = array();
		$giveDataStep1 = false;
		$xml = $this->getXML();
		$step = 0;
		$langs = array();
		$handle = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."international.xml"));
		$xpathLangs = $handle->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[@iso != '']");
		foreach ($xpathLangs as $lang)
			if (!in_array(trim($lang), $langs))
				array_push($langs, trim($lang));
		array_multisort($langs, SORT_STRING, SORT_ASC);
		$xml->startTag("langs");
		foreach ($langs as $lang)
			$xml->addFullTag("lang", $lang, true);
		$xml->endTag("langs");
				
		// If reload
		if ($this->_http->getParam('reload_international_step1') == "true")
		{
			// If one lang at least
			$listLangs = SLS_String::trimSlashesFromString($this->_http->getParam("international_langs"));			
			if (empty($listLangs))
				array_push($errors,"You must choose at least one language");
			else
			{
				$xmlIsos = "";
				
				foreach($listLangs as $listLang)
				{
					$iso = array_shift($handle->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[node()='".$listLang."']/@iso"));
					$xmlIsos .= '<name isSecure="false" js="false" active="true"><![CDATA['.$iso.']]></name>';
					if (is_file($this->_generic->getPathConfig("installDeployement")."Langs/Generics/generic.".$iso.".lang.php"))
						copy($this->_generic->getPathConfig("installDeployement")."Langs/Generics/generic.".$iso.".lang.php", $this->_generic->getPathConfig("coreGenericLangs")."generic.".$iso.".lang.php");
				}
				
			}
			if (empty($errors))
			{
				$step = 1;
				$coreXml = $this->_generic->getSiteXML();
				$coreXml->setTag('langs',$xmlIsos,false);
				file_put_contents($this->_generic->getPathConfig("configSecure")."site.xml", $coreXml->getXML());
				$giveDataStep1 = true;
			}
			else
			{
				$step = 0;
				$xml->startTag("errors");
				foreach ($errors as $error)
					$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
			}
		}
		// Set controllers.xml
		else if ($this->_http->getParam('reload_international_step2') == "true")
		{
			$langs = $this->_generic->getSiteXML()->getTags("//configs/langs/name");
			$listLangs = explode("-", SLS_String::trimSlashesFromString($this->_http->getParam("international_languages")));
			$params = $this->_http->getParams('post');
			
			$userValues = array();
			foreach ($langs as $lang)
				$userValues[$lang] = array();
			
			foreach ($params as $key=>$param)
				if (array_key_exists(SLS_String::substrBeforeFirstDelimiter($key, '_'), $userValues))
					$userValues[SLS_String::substrBeforeFirstDelimiter($key, '_')][SLS_String::substrAfterFirstDelimiter($key, '_')] = SLS_String::trimSlashesFromString($param);
			
			// Check errors
			$errors = array();
			$xml->startTag("InternationalMemory");
			$xml->addFullTag("default", SLS_String::trimSlashesFromString($this->_http->getParam("default_lang")), true);
			foreach ($userValues as $key=>$values) 
			{
				$mods[$key] = array();
				$smods[$key]['home'] = array();
				$smods[$key]['error'] = array();
				
				foreach ($values as $name=>$value)
				{
					$xml->startTag("row");
					$xml->addFullTag("name", $key."_".$name, true);
					$xml->addFullTag("value", SLS_String::trimSlashesFromString($value), true);
					$xml->endTag("row");
					if (substr($name, 0, 11) == "TRANSLATION")
					{
						if (empty($value))
							array_push($errors, "You must fill the translation for ".ucwords(strtolower(str_replace("_", " ", substr($name, 11))))." in ".strtoupper($key));
					}					
				}
				
				if (empty($values['home_mod']))
					array_push($errors, "You must fill a value for the URL of Main Controller in ".strtoupper($key));				
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['home_mod']),"")!=strtolower($values['home_mod']))
					array_push($errors, "You must fill a value for the URL of Main Controller without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($mods[$key], $values['home_mod']);				
				if (empty($values['home_desc']))
					array_push($errors, "You must fill a page title for your home page in ".strtoupper($key));
				if (empty($values['home_index']))
					array_push($errors, "You must fill an action value for your home page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['home_index']),"")!=strtolower($values['home_index']))
					array_push($errors, "You must fill an action value for your home page without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['home'], $values['home_index']);
				if (empty($values['error_mod']))
					array_push($errors, "You must fill a value for the URL of Error Controller in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_mod']),"")!=strtolower($values['error_mod']))
					array_push($errors, "You must fill a value for the URL of Error Controller without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($mods[$key], $values['error_mod']);
				if (empty($values['error_400_desc']))
					array_push($errors, "You must fill a page title for your 400 page in ".strtoupper($key));
				if (empty($values['error_401_desc']))
					array_push($errors, "You must fill a page title for your 401 page in ".strtoupper($key));
				if (empty($values['error_403_desc']))
					array_push($errors, "You must fill a page title for your 403 page in ".strtoupper($key));
				if (empty($values['error_404_desc']))
					array_push($errors, "You must fill a page title for your 404 page in ".strtoupper($key));
				if (empty($values['error_500_desc']))
					array_push($errors, "You must fill a page title for your 500 page in ".strtoupper($key));
				if (empty($values['error_307_desc']))
					array_push($errors, "You must fill a page title for your 307 page in ".strtoupper($key));
				if (empty($values['error_302_desc']))
					array_push($errors, "You must fill a page title for your 302 page in ".strtoupper($key));
					
				if (empty($values['error_400_url']))
					array_push($errors, "You must fill a value for the URL of your 400 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_400_url']),"")!=strtolower($values['error_400_url']))
					array_push($errors, "You must fill an action value for the URL of you 400 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_400_url']);
				if (empty($values['error_401_url']))
					array_push($errors, "You must fill a value for the URL of your 401 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_401_url']),"")!=strtolower($values['error_401_url']))
					array_push($errors, "You must fill an action value for the URL of you 401 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_401_url']);
				if (empty($values['error_403_url']))
					array_push($errors, "You must fill a value for the URL of your 403 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_403_url']),"")!=strtolower($values['error_403_url']))
					array_push($errors, "You must fill an action value for the URL of you 403 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_403_url']);
				if (empty($values['error_404_url']))
					array_push($errors, "You must fill a value for the URL of your 404 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_404_url']),"")!=strtolower($values['error_404_url']))
					array_push($errors, "You must fill an action value for the URL of you 404 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_404_url']);
				if (empty($values['error_500_url']))
					array_push($errors, "You must fill a value for the URL of your 500 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_500_url']),"")!=strtolower($values['error_500_url']))
					array_push($errors, "You must fill an action value for the URL of you 500 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_500_url']);
				if (empty($values['error_307_url']))
					array_push($errors, "You must fill a value for the URL of your 307 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_307_url']),"")!=strtolower($values['error_307_url']))
					array_push($errors, "You must fill an action value for the URL of you 307 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_307_url']);
				if (empty($values['error_302_url']))
					array_push($errors, "You must fill a value for the URL of your 302 page in ".strtoupper($key));
				else if (SLS_String::stringToUrl(SLS_String::trimSlashesFromString($values['error_302_url']),"")!=strtolower($values['error_302_url']))
					array_push($errors, "You must fill an action value for the URL of you 302 without spaces, accented characters or specials characters in ".strtoupper($key));
				else 
					array_push($smods[$key]['error'], $values['error_302_url']);
				
				$unikUrl = array();
				foreach ($smods[$key]['error'] as $smod)
					if (!in_array($smod, $unikUrl))
						array_push($unikUrl, $smod);
				
				if (count($unikUrl) != count($smods[$key]['error']))
					array_push($errors, "You cannot set the same Action name for two differents actions in the same language : ".strtoupper($key));
					
			}
			
			$xml->endTag("InternationalMemory");
			if (empty($errors))
			{
				$caIds = array();
				// Set defaut lang
				$siteXML = $this->_generic->getSiteXML();
				$siteXML->setTag("defaultLang", SLS_String::trimSlashesFromString($this->_http->getParam("default_lang")));
				$siteXML->setTagAttributes("//configs/domainName/domain[@default='1']",array("lang" => SLS_String::trimSlashesFromString($this->_http->getParam("default_lang"))));
				file_put_contents($this->_generic->getPathConfig("configSecure")."site.xml", $siteXML->getXML());
				$langs = $this->_generic->getSiteXML()->getTags("//configs/langs/name");
				
				$xmlControllers = $this->_generic->getControllersXML();
				
				// Generate the Home Controller ID and the Default Controller ID
				$homeID = $this->_generic->generateControllerId();
				array_push($caIds, $homeID);
				
				$defaultID = $this->_generic->generateControllerId();
				while (in_array($defaultID, $caIds))
					$defaultID = $this->_generic->generateControllerId();
				array_push($caIds, $defaultID);
				
				// Generate Actions IDs
				// Home/Index
				$indexID = $this->_generic->generateActionId();
				array_push($caIds, $indexID);
				
				// Default/UrlError
				$urlErrorID = $this->_generic->generateActionId();
				while (in_array($urlErrorID, $caIds))
					$urlErrorID = $this->_generic->generateActionId();
				array_push($caIds, $urlErrorID);
				
				// Default/BadRequestError
				$badRequestID = $this->_generic->generateActionId();
				while (in_array($badRequestID, $caIds))
					$badRequestID = $this->_generic->generateActionId();
				array_push($caIds, $badRequestID);
				
				// Default/AuthorizationError
				$authorizationID = $this->_generic->generateActionId();
				while (in_array($authorizationID, $caIds))
					$authorizationID = $this->_generic->generateActionId();
				array_push($caIds, $authorizationID);
				
				// Default/ForbiddenError
				$forbiddenID = $this->_generic->generateActionId();
				while (in_array($forbiddenID, $caIds))
					$forbiddenID = $this->_generic->generateActionId();
				array_push($caIds, $forbiddenID);
				
				// Default/InternalServerError	
				$serverID = $this->_generic->generateActionId();
				while (in_array($serverID, $caIds))
					$serverID = $this->_generic->generateActionId();
				array_push($caIds, $serverID);
				
				// Default/TemporaryRedirectError	
				$redirectID = $this->_generic->generateActionId();
				while (in_array($redirectID, $caIds))
					$redirectID = $this->_generic->generateActionId();
				array_push($caIds, $redirectID);
				
				// Default/MaintenanceError	
				$maintenanceID = $this->_generic->generateActionId();
				while (in_array($maintenanceID, $caIds))
					$maintenanceID = $this->_generic->generateActionId();
				array_push($caIds, $maintenanceID);
								
				$controllerUser['home']['mod'] = "<controller name=\"Home\" side=\"user\" id=\"".$homeID."\"><controllerLangs>";
				$controllerUser['home']['smod'] = "<scontrollers><scontroller name=\"Index\" needParam=\"0\" id=\"".$indexID."\" default=\"1\"><scontrollerLangs>";
				$controllerUser['default']['mod'] = "<controller name=\"Default\" side=\"user\" id=\"".$defaultID."\"><controllerLangs>";
				$controllerUser['default']['smod']['404'] = "<scontrollers><scontroller name=\"UrlError\" needParam=\"0\" id=\"".$urlErrorID."\"><scontrollerLangs>";
				$controllerUser['default']['smod']['400'] = "<scontroller name=\"BadRequestError\" needParam=\"0\" id=\"".$badRequestID."\"><scontrollerLangs>";
				$controllerUser['default']['smod']['401'] = "<scontroller name=\"AuthorizationError\" needParam=\"0\" id=\"".$authorizationID."\"><scontrollerLangs>";
				$controllerUser['default']['smod']['403'] = "<scontroller name=\"ForbiddenError\" needParam=\"0\" id=\"".$forbiddenID."\"><scontrollerLangs>";
				$controllerUser['default']['smod']['500'] = "<scontroller name=\"InternalServerError\" needParam=\"0\" id=\"".$serverID."\"><scontrollerLangs>";
				$controllerUser['default']['smod']['307'] = "<scontroller name=\"TemporaryRedirectError\" needParam=\"0\" id=\"".$redirectID."\"><scontrollerLangs>";
				$controllerUser['default']['smod']['302'] = "<scontroller name=\"MaintenanceError\" needParam=\"0\" id=\"".$maintenanceID."\"><scontrollerLangs>";
				
				$paramsPost = $this->_http->getParams('post');
				
				$handleLangs = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."international.xml"));
				$projectName = array_shift($this->_generic->getSiteXML()->getTags("projectName"));
				
				/* Create Lang files and Lang Directory
				mkdir($this->_generic->getPathConfig("actionLangs")."Home");
				mkdir($this->_generic->getPathConfig("actionLangs")."Default");	
				*/
				$metasXML = $this->_generic->getCoreXML('metas');
				// Add Empty Actions in metas
				foreach ($caIds as $aId)
				{
					$str = "<action id=\"".$aId."\"></action>";
					$metasXML->appendXMLNode("//sls_configs", $str);
				}
				
				// Foreach langs
				foreach ($langs as $lang) 
				{
					// Home controller
					$controllerUser['home']['mod'] 		.= "<controllerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_home_mod"))."]]></controllerLang>";
					$controllerUser['home']['smod'] 	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_home_index"))."]]></scontrollerLang>";
					
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_home_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_home_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_home_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$indexID."']", $strTitle);
					
					// Default controller
					$controllerUser['default']['mod'] 			.= "<controllerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_mod"))."]]></controllerLang>";
					$controllerUser['default']['smod']['404']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_404_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_404_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_404_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_404_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$urlErrorID."']", $strTitle);
					
					$controllerUser['default']['smod']['400']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_400_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_400_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_400_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_400_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$badRequestID."']", $strTitle);
					
					$controllerUser['default']['smod']['401']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_401_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_401_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_401_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_401_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$authorizationID."']", $strTitle);
					
					$controllerUser['default']['smod']['403']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_403_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_403_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_403_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_403_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$forbiddenID."']", $strTitle);
					
					$controllerUser['default']['smod']['500']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_500_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_500_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_500_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_500_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$serverID."']", $strTitle);
					
					$controllerUser['default']['smod']['307']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_307_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_307_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_307_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_307_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$redirectID."']", $strTitle);
					
					$controllerUser['default']['smod']['302']	.= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_302_url"))."]]></scontrollerLang>";
					$strTitle = "<title lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_302_desc"))."]]></title>";
					$strTitle .= "<description lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_302_description"))."]]></description>";
					$strTitle .= "<keywords lang=\"".$lang."\"><![CDATA[".SLS_String::trimSlashesFromString($this->_http->getParam($lang."_error_302_keywords"))."]]></keywords>";
					$metasXML->appendXMLNode("//sls_configs/action[@id='".$maintenanceID."']", $strTitle);
					
					// Generic langs					
					$genericFile = "<?\n/**\n * Generic Sls Vars\n */\n";
					$length = strlen($lang."_TRANSLATION");
					foreach ($paramsPost as $key=>$value)
					{
						$value = SLS_String::trimSlashesFromString($value);
						
						if (substr($key, 0, $length) == $lang."_TRANSLATION")
						{
							$genericFile .= '$GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'SLS_'.substr($key, $length+1).'\'] = "'.$value.'";';
							$genericFile .= "\n";
						}	
						
					}
					$genericFile .= "?>";
					file_put_contents($this->_generic->getPathConfig("coreGenericLangs")."generic.".$lang.".lang.php", $genericFile);
					
					// Generic lang site
					$language = array_shift($handleLangs->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[@iso = '".$lang."']"));
					$contentSiteLang = "<?\n/**\n * ".$projectName." Translations\n * Language : ".ucwords($language)." (".strtoupper($lang).")\n */\n\n?>";
					file_put_contents($this->_generic->getPathConfig('genericLangs')."site.".$lang.".lang.php", $contentSiteLang);
				}
				
				// Controllers
				$controllerUser['home']['mod'] 				.= "</controllerLangs>";
				$controllerUser['home']['smod']				.= "</scontrollerLangs></scontroller></scontrollers></controller>";
				
				$controllerUser['default']['mod']			.= "</controllerLangs>";
				$controllerUser['default']['smod']['404']	.= "</scontrollerLangs></scontroller>";
				$controllerUser['default']['smod']['400']	.= "</scontrollerLangs></scontroller>";
				$controllerUser['default']['smod']['401']	.= "</scontrollerLangs></scontroller>";
				$controllerUser['default']['smod']['403']	.= "</scontrollerLangs></scontroller>";
				$controllerUser['default']['smod']['307']	.= "</scontrollerLangs></scontroller>";
				$controllerUser['default']['smod']['302']	.= "</scontrollerLangs></scontroller>";
				$controllerUser['default']['smod']['500']	.= "</scontrollerLangs></scontroller></scontrollers></controller>";
				
				// Formation du Flux Final a append
				$flux = $controllerUser['home']['mod'].$controllerUser['home']['smod'].$controllerUser['default']['mod'].$controllerUser['default']['smod']['404'].$controllerUser['default']['smod']['400'].$controllerUser['default']['smod']['401'].$controllerUser['default']['smod']['403'].$controllerUser['default']['smod']['307'].$controllerUser['default']['smod']['302'].$controllerUser['default']['smod']['500'];
				$xmlControllers->appendXMLNode('//controllers', $flux);
				file_put_contents($this->_generic->getPathConfig("configSecure")."controllers.xml", $xmlControllers->getXML());
				
				// Add meta Tags
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$indexID."']", "<robots><![CDATA[index, follow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$urlErrorID."']", "<robots><![CDATA[noindex, follow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$authorizationID."']", "<robots><![CDATA[noindex, nofollow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$serverID."']", "<robots><![CDATA[noindex, nofollow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$forbiddenID."']", "<robots><![CDATA[noindex, nofollow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$badRequestID."']", "<robots><![CDATA[noindex, nofollow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$redirectID."']", "<robots><![CDATA[noindex, nofollow]]></robots>");
				$metasXML->appendXMLNode("//sls_configs/action[@id='".$maintenanceID."']", "<robots><![CDATA[noindex, nofollow]]></robots>");
				file_put_contents($this->_generic->getPathConfig("configSls")."metas.xml", $metasXML->getXML());
				// Déplacement des Fichiers de déploiement
				
				// Controllers
				if (!is_dir($this->_generic->getPathConfig("actionsControllers")."Home"))
					mkdir($this->_generic->getPathConfig("actionsControllers")."Home");
				if (!is_dir($this->_generic->getPathConfig("actionsControllers")."Default"))
					mkdir($this->_generic->getPathConfig("actionsControllers")."Default");
					
				// Langs
				if (!is_dir($this->_generic->getPathConfig("actionLangs")."Home"))
					mkdir($this->_generic->getPathConfig("actionLangs")."Home");
				if (!is_dir($this->_generic->getPathConfig("actionLangs")."Default"))
					mkdir($this->_generic->getPathConfig("actionLangs")."Default");
				
					
				// Generic Site Protected functions
				copy($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/__site.protected.php", $this->_generic->getPathConfig("actionsControllers")."__site.protected.php");
				
				$homeFiles = scandir($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/Home");
				$defaultFiles = scandir($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/Default");

				// Copy Home Files
				foreach ($homeFiles as $file)
				{
					if (substr($file, (strlen($file)-3)) == "php")
					{ 
						copy($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/Home/".$file, $this->_generic->getPathConfig("actionsControllers")."Home/".$file);
						if (SLS_String::startsWith($file, "__"))
						{
							foreach ($langs as $lang)
							{
								$strLang = '<?php'."\n".
													'/**'."\n".
													'* '.strtoupper($lang).' File for all the Controller Home'."\n".
													'* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
													'* '.t(1).'Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'* '.t(1).'Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'*'."\n".
													'* '.t(1).'You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'" '."\n".
													'* @author SillySmart'."\n".
													'* @copyright SillySmart'."\n".
													'* @package Langs.Actions.'.$controller."\n".
													'* @since 1.0'."\n".
													'*'."\n".
													'*/'."\n".
													'?>';
								file_put_contents($this->_generic->getPathConfig("actionLangs")."Home/__Home.".strtolower($lang).".lang.php", $strLang);
							}
						}
						else 
						{
							
							$actionName = trim(SLS_String::substrBeforeFirstDelimiter($file, ".controller"));
							foreach ($langs as $lang)
							{
								$strLang = '<?php'."\n".
													'/**'."\n".
													'* '.strtoupper($lang).' File for the action '.$actionName.' into Home Controller'."\n".
													'* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
													'* '.t(1).'Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'* '.t(1).'Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'*'."\n".
													'* '.t(1).'You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'" '."\n".
													'* @author SillySmart'."\n".
													'* @copyright SillySmart'."\n".
													'* @package Langs.Actions.Home'."\n".
													'* @since 1.0'."\n".
													'*'."\n".
													'*/'."\n".
													'?>';
								file_put_contents($this->_generic->getPathConfig("actionLangs")."Home/".$actionName.".".strtolower($lang).".lang.php", $strLang);
							}
						}
					}
				}
				
				// Copy Default Files
				foreach ($defaultFiles as $file)
				{
					if (substr($file, (strlen($file)-3)) == "php")
					{ 
						copy($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/Default/".$file, $this->_generic->getPathConfig("actionsControllers")."Default/".$file);
						if (SLS_String::startsWith($file, "__"))
						{
							foreach ($langs as $lang)
							{
								$strLang = '<?php'."\n".
													'/**'."\n".
													'* '.strtoupper($lang).' File for all the Controller Default'."\n".
													'* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
													'* '.t(1).'Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'* '.t(1).'Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'*'."\n".
													'* '.t(1).'You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'" '."\n".
													'* @author SillySmart'."\n".
													'* @copyright SillySmart'."\n".
													'* @package Langs.Actions.Default'."\n".
													'* @since 1.0'."\n".
													'*'."\n".
													'*/'."\n".
													'?>';
								file_put_contents($this->_generic->getPathConfig("actionLangs")."Default/__Default.".strtolower($lang).".lang.php", $strLang);
							}
						}
						else 
						{
							
							$actionName = trim(SLS_String::substrBeforeFirstDelimiter($file, ".controller"));
							foreach ($langs as $lang)
							{
								$strLang = '<?php'."\n".
													'/**'."\n".
													'* '.strtoupper($lang).' File for the action '.$actionName.' into Default Controller'."\n".
													'* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
													'* '.t(1).'Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'* '.t(1).'Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
													'*'."\n".
													'* '.t(1).'You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'" '."\n".
													'* @author SillySmart'."\n".
													'* @copyright SillySmart'."\n".
													'* @package Langs.Actions.Home'."\n".
													'* @since 1.0'."\n".
													'*'."\n".
													'*/'."\n".
													'?>';
								file_put_contents($this->_generic->getPathConfig("actionLangs")."Default/".$actionName.".".strtolower($lang).".lang.php", $strLang);
							}
						}
					}
				}
					
				// Views
				if (!is_dir($this->_generic->getPathConfig("viewsBody")."Home"))
					mkdir($this->_generic->getPathConfig("viewsBody")."Home");
				if (!is_dir($this->_generic->getPathConfig("viewsBody")."Default"))
					mkdir($this->_generic->getPathConfig("viewsBody")."Default");
				if (!is_dir($this->_generic->getPathConfig("viewsHeaders")."Home"))
					mkdir($this->_generic->getPathConfig("viewsHeaders")."Home");
				if (!is_dir($this->_generic->getPathConfig("viewsHeaders")."Default"))
					mkdir($this->_generic->getPathConfig("viewsHeaders")."Default");
				
				$homeBodyFiles = scandir($this->_generic->getPathConfig("installDeployement")."Views/Body/Home");
				$defaultBodyFiles = scandir($this->_generic->getPathConfig("installDeployement")."Views/Body/Default");
				$homeHeadersFiles = scandir($this->_generic->getPathConfig("installDeployement")."Views/Headers/Home");
				$defaultHeadersFiles = scandir($this->_generic->getPathConfig("installDeployement")."Views/Headers/Default");

				// Copy Home Body Views
				foreach ($homeBodyFiles as $file)
					(substr($file, (strlen($file)-3)) == "xsl") ? copy($this->_generic->getPathConfig("installDeployement")."Views/Body/Home/".$file, $this->_generic->getPathConfig("viewsBody")."Home/".$file) : "";
				
				// Copy Default Body Views
				foreach ($defaultBodyFiles as $file)
					(substr($file, (strlen($file)-3)) == "xsl") ? copy($this->_generic->getPathConfig("installDeployement")."Views/Body/Default/".$file, $this->_generic->getPathConfig("viewsBody")."Default/".$file) : "";	
					
				// Copy Home Headers Views
				foreach ($homeHeadersFiles as $file)
					(substr($file, (strlen($file)-3)) == "xsl") ? copy($this->_generic->getPathConfig("installDeployement")."Views/Headers/Home/".$file, $this->_generic->getPathConfig("viewsHeaders")."Home/".$file) : "";
				
				// Copy Default Headers Views
				foreach ($defaultHeadersFiles as $file)
					(substr($file, (strlen($file)-3)) == "xsl") ? copy($this->_generic->getPathConfig("installDeployement")."Views/Headers/Default/".$file, $this->_generic->getPathConfig("viewsHeaders")."Default/".$file) : "";	
				
				
								
				$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"DataBase",1=>"DataBase"));
				return $this->_generic->dispatch("SLS_Init", "DataBase");				
			}
			else 
			{
				$xml->startTag('errors');			
				foreach ($errors as $error)
					$xml->addFullTag('error', $error, true);
				$xml->endTag('errors');
				$giveDataStep1 = true;
				$step = 1;
			}
			
		}
		// Sinon, default
		else
		{
			$step = 0;
		}
		if ($giveDataStep1 == true)
		{
			$xml->startTag("choose_langs");
			$valueToHidden = "";
			$isos = array();
			foreach($listLangs as $listLang)
			{
				$iso = array_shift($handle->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[node()='".$listLang."']/@iso"));
				array_push($isos, $iso);
				$xml->startTag("choose_lang");					
				$xml->addFullTag("iso",$iso,true);
				$xml->addFullTag("label",$listLang,true);
				$xml->endTag("choose_lang");
				$valueToHidden .= "-".$listLang;
			}
			$xml->endTag("choose_langs");
			$xml->addFullTag("hidden_langs", substr($valueToHidden, 1), true);
			// Récupération des mots à traduire
			$xml->startTag("translate");
			foreach ($isos as $iso)
			{
				$xml->startTag($iso);
					if (is_file($this->_generic->getPathConfig("coreGenericLangs")."generic.".$iso.".lang.php"))
						$handle = fopen($this->_generic->getPathConfig("coreGenericLangs")."generic.".$iso.".lang.php", 'r');
					else 
						$handle = fopen($this->_generic->getPathConfig("coreGenericLangs")."generic.en.lang.php", 'r');
					$array = array();
					while (!feof($handle)) 
					{
						$line = fgets($handle, 4096);
						if (substr($line, 0, 1) == "$")
						{
							$tmpArray = array();
							$tmpArray['name'] =  str_replace("SLS_", "", SLS_String::substrAfterLastDelimiter(SLS_String::substrBeforeLastDelimiter($line, "']"), "['"));
							$tmpArray['value'] = SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterFirstDelimiter(trim(SLS_String::substrAfterLastDelimiter($line, " = ")), '"'), '"');
							array_push($array, $tmpArray);
						}
					}
					
					foreach ($array as $row)
					{
						$xml->startTag("sentence");
							$xml->addFullTag('name', strtolower(str_replace("_", " ", $row['name'])), true);
							$xml->addFullTag('code', $row['name'], true);
							$xml->addFullTag('value', $row['value'], true);
						$xml->endTag("sentence");
					}
				$xml->endTag($iso);
			}
			$xml->endTag("translate");
		}
		$xml->addFullTag("step", $step, true);
		$this->saveXML($xml);
	}
	
}
?>