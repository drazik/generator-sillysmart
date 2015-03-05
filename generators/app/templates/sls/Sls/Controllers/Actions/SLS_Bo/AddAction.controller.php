<?php
class SLS_BoAddAction extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		$listing = true;
		$errors = array();
		$protocol = $this->_generic->getProtocol();
		$siteXML = $this->_generic->getSiteXML();
		$aliasesSelected = array();
		$componentsSelected = array();
		
		$plugin = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configPlugins")."/plugins.xml"));
		$pluginMobile = $plugin->getTag("//plugins/plugin[@code='mobile']");
		$pluginMobile = (!empty($pluginMobile)) ? true : false;
				
		$controllersXML = $this->_generic->getControllersXML();
		
		$controller = SLS_String::trimSlashesFromString($this->_http->getParam("Controller"));
		$controllers = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']");
		if (count($controllers) == 1)
		{
			$controllerID = array_shift($controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/@id"));
			$protocol = $this->_generic->getControllerProtocol($controllerID);
			$listing = false;
			$xml->addFullTag('request', 'AddAction', true);
			
			if ($this->_http->getParam('reload') == 'true')
			{
				// Get the form informations
				$newAction 				= SLS_String::stringToUrl(ucwords(SLS_String::trimSlashesFromString($this->_http->getParam("actionName"))), "", false);
				$needDynamic 			= SLS_String::trimSlashesFromString($this->_http->getParam("dynamic"));
				$needOffline 			= SLS_String::trimSlashesFromString($this->_http->getParam("offline"));
				$needDefault 			= SLS_String::trimSlashesFromString($this->_http->getParam("default"));
				$searchEngine			= SLS_String::trimSlashesFromString($this->_http->getParam("indexes"));
				$postProtocol			= SLS_String::trimSlashesFromString($this->_http->getParam("protocol"));
				$tpl 					= SLS_String::trimSlashesFromString($this->_http->getParam('template'));
				$aliases				= SLS_String::trimSlashesFromString($this->_http->getParam('domains'));
				$components				= SLS_String::trimSlashesFromString($this->_http->getParam('components'));
				$cache_visibility 		= SLS_String::trimSlashesFromString($this->_http->getParam('cache_visibility'));
				$cache_scope 			= SLS_String::trimSlashesFromString($this->_http->getParam('cache_scope'));
				$cache_expiration 		= SLS_String::trimSlashesFromString($this->_http->getParam('cache_expiration'));
				$cache_responsive 		= SLS_String::trimSlashesFromString($this->_http->getParam('cache_responsive'));
				if (empty($postProtocol))
					$postProtocol = "http";
				$postActionsLang		= array();
				$postTitlesLang			= array();
				$postDescriptionsLang	= array();
				$postKeywordsLang		= array();	
				$toCache			 	= (in_array($cache_visibility,array("public","private"))) ? true : false;
				
				// If responsive wanted
				if ($cache_responsive == "true" && !$pluginMobile)
				{
					// Force Mobile plugin download
					file_get_contents($this->_generic->getFullPath("SLS_Bo",
																  "SearchPlugin",
																  array("Action" => "Download",
																  		"Server" => "4",
																  		"Plugin" => "20",
																  		"token"	 => sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))),
																  true));
				}
				
				if (empty($newAction))
					array_push($errors, "Action name can't be empty");
								
				$actionExist 		= $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@name='".$newAction."']");
				if (count($actionExist) == 0)
				{
					if (empty($postProtocol) || ($postProtocol != 'http' && $postProtocol != 'https'))
						array_push($errors, "Protocol must be http or https");
					else 
						$protocol = $postProtocol;
										
					if (!empty($aliases) && is_array($aliases))
						foreach($aliases as $alias)
							array_push($aliasesSelected,$alias);
							
					if (!empty($components) && is_array($components))
						foreach($components as $component)
							array_push($componentsSelected,$component);
						
					$siteLangs = $this->_generic->getObjectLang()->getSiteLangs();
					
					foreach ($siteLangs as $lang)
					{
						// Check Url
						$postLang = trim(SLS_String::stringToUrl(SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-action")), "", false));
						$translationExist = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller/scontrollerLangs[scontrollerLang = '".$postLang."']");
						if (empty($postLang))
							array_push($errors, "You need to fill the ".$lang." url translations");
						elseif(count($translationExist) != 0)
							array_push($errors, "You URL translation in ".$lang." is already in use on another action in the same controller");
						else
							$postActionsLang[$lang] =  $postLang;
						
						// Get Titles	
						$postTitlesLang[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-title"));
						// Get Description
						$postDescriptionsLang[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-description"));
						// Get Keywords
						$postKeywordsLang[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-keywords"));
					}
					
					if ($toCache && (!is_numeric($cache_expiration) || $cache_expiration < 0))
						array_push($errors, "Your expiration cache must be a positive time or 0");
					if ($toCache && (!in_array($cache_scope,array("full","partial"))))
						array_push($errors, "Your must describe your cache scope");
					
					if (count($errors) == 0)
					{
						// If an error existing and the controller directory wasn't created
						if (!is_dir($this->_generic->getPathConfig("actionsControllers").$controller))
							mkdir($this->_generic->getPathConfig("actionsControllers").$controller);
						if (!is_file($this->_generic->getPathConfig("actionsControllers").$controller."/__".$controller.".protected.php"))
						{
							$strControllerProtected = '<?php'."\n".
							   '/**'."\n".
							   '* Class generic for the controller '.$newControllerName.''."\n".
							   '* Write here all your generic functions you need in your '.$newControllerName.' Actions'."\n".
							   '* @author SillySmart'."\n".
							   '* @copyright SillySmart'."\n".
							   '* @package Mvc.Controllers.'.$newControllerName.''."\n".
							   '* @see Mvc.Controllers.SiteProtected'."\n".
							   '* @see Sls.Controllers.Core.SLS_GenericController'."\n".
							   '* @since 1.0'."\n".
							   '*/'."\n".
							   'class '.$newControllerName.'ControllerProtected extends SiteProtected'."\n".
							   '{'."\n".
							   t(1).'public function init()'."\n".
							   t(1).'{'."\n".
							   		t(2).'parent::init();'."\n".
							   t(1).'}'."\n".
							   '}'."\n".
							   '?>';
						
							file_put_contents($this->_generic->getPathConfig("actionsControllers").$controller."/__".$controller.".protected.php", $strControllerProtected);
						}
						
						// Create Controller File
						$strControllerAction = '<?php'."\n".
												'/**'."\n".
												'* Class '.$newAction.' into '.$controller.' Controller'."\n".
												'* @author SillySmart'."\n".
												'* @copyright SillySmart'."\n".
												'* @package Mvc.Controllers.'.$controller."\n".
												'* @see Mvc.Controllers.'.$controller.'.ControllerProtected'."\n".
												'* @see Mvc.Controllers.SiteProtected'."\n".
												'* @see Sls.Controllers.Core.SLS_GenericController'."\n".
												'* @since 1.0'."\n".
												'*'."\n".
												'*/'."\n".
												'class '.$controller.$newAction.' extends '.$controller.'ControllerProtected'."\n".
												'{'."\n".
												t(1).'public function init()'."\n".
											   	t(1).'{'."\n".
											   		t(2).'parent::init();'."\n".
											   	t(1).'}'."\n\n".
												t(1).'public function action()'."\n".
												t(1).'{'."\n".
													t(2)."\n".
												t(1).'}'."\n".
												"}\n".
												'?>';
						file_put_contents($this->_generic->getPathConfig("actionsControllers").$controller."/".$newAction.".controller.php", $strControllerAction);
						
						// Create Lang Files
						if (!is_dir($this->_generic->getPathConfig("langs")."Actions/".$controller))
							mkdir($this->_generic->getPathConfig("langs")."Actions/".$controller);
						$langsFiles = array();
						
						foreach ($siteLangs as $lang)
						{
							$strLang = '<?php'."\n".
												'/**'."\n".
												'* '.strtoupper($lang).' File for the action '.$newAction.' into '.$controller.' Controller'."\n".
												'* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
												'* '."\t".'Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
												'* '."\t".'Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
												'*'."\n".
												'* '."\t".'You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'" '."\n".
												'* @author SillySmart'."\n".
												'* @copyright SillySmart'."\n".
												'* @package Langs.Actions.'.$controller."\n".
												'* @since 1.0'."\n".
												'*'."\n".
												'*/'."\n".
												'?>';
							file_put_contents($this->_generic->getPathConfig("langs")."Actions/".$controller."/".$newAction.".".strtolower($lang).".lang.php", $strLang);
						}
						
						// Create Views File
						if (!is_dir($this->_generic->getPathConfig("viewsBody").$controller))
							mkdir($this->_generic->getPathConfig("viewsBody").$controller);
						if (!is_dir($this->_generic->getPathConfig("viewsHeaders").$controller))
							mkdir($this->_generic->getPathConfig("viewsHeaders").$controller);
						
						$strBody = '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">'."\n".
									t(1).'<xsl:template name="'.$newAction.'">'."\n".
										t(2).'<h1>Customize the body of this page in <i>'.$this->_generic->getPathConfig('viewsBody').$controller.'/'.$newAction.'.xsl</i></h1>'."\n".
										t(2).'<h2>And your headers in <i>'.$this->_generic->getPathConfig('viewsHeaders').$controller.'/'.$newAction.'.xsl</i></h2>'."\n".
									t(1).'</xsl:template>'."\n".
									'</xsl:stylesheet>';
						$strHeader = 	'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">'."\n".
										t(1).'<xsl:template name="Header'.$newAction.'">'."\n".
										t(1).'</xsl:template>'."\n".
										'</xsl:stylesheet>';
						file_put_contents($this->_generic->getPathConfig("viewsBody").$controller.'/'.$newAction.'.xsl', $strBody);
						file_put_contents($this->_generic->getPathConfig("viewsHeaders").$controller.'/'.$newAction.'.xsl', $strHeader);
						// End of files creation
						
						// XML Modifications
						$metasXML = $this->_generic->getCoreXML('metas');
						// Get the titles
						
						// If default, reset other in current controller
						if (!empty($needDefault))
						{
							$actions = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller/@id");
							foreach($actions as $curActionId)
								$controllersXML->setTagAttributes("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@id='".$curActionId."']", array('default' => '0'));
						}
												
						$actionId = $this->_generic->generateActionId();
						$xmlAction = "<scontroller name=\"".$newAction."\" needParam=\"";
						$xmlAction .= ($this->_http->getParam("dynamic") == "on") ? "1" : "0";
						$xmlAction .= "\" id=\"".$actionId."\" protocol=\"".$protocol."\"";
						if ($tpl != -1)
							$xmlAction .= " tpl=\"".$tpl."\"";
						if (!empty($aliases))
							$xmlAction .= " domains=\"".implode(",",$aliases)."\"";
						if (!empty($components))
							$xmlAction .= " components=\"".implode(",",$components)."\"";
						if (!empty($needOffline))
							$xmlAction .= " disable=\"1\"";
						if (!empty($needDefault))
							$xmlAction .= " default=\"1\"";
						if ($toCache)
							$xmlAction .= " cache=\"".$cache_visibility."|".$cache_scope."|".(($cache_responsive == "true") ? "responsive" : "no_responsive")."|".$cache_expiration."\"";
						$xmlAction .= "><scontrollerLangs>";
						$strMetas = "<action id=\"".$actionId."\" />";
						$metasXML->appendXMLNode("//sls_configs", $strMetas);
						foreach ($siteLangs as $lang)
						{
							$xmlAction .= "<scontrollerLang lang=\"".$lang."\"><![CDATA[".$postActionsLang[$lang]."]]></scontrollerLang>";
							// Metas
							$metas = "<title lang=\"".$lang."\"><![CDATA[".$postTitlesLang[$lang]."]]></title>";
							$metas .= "<description lang=\"".$lang."\"><![CDATA[".$postDescriptionsLang[$lang]."]]></description>";
							$metas .= "<keywords lang=\"".$lang."\"><![CDATA[".$postKeywordsLang[$lang]."]]></keywords>";
							$metasXML->appendXMLNode("//sls_configs/action[@id=\"".$actionId."\"]", $metas);							
						}
						$xmlAction .= "</scontrollerLangs></scontroller>";
						if (!SLS_String::contains($searchEngine,", "))
							$searchEngine = str_replace(",",", ",$searchEngine);
						if ($searchEngine != "index, follow" && $searchEngine != "noindex, follow" && $searchEngine != "noindex, nofollow" && $searchEngine != "index, nofollow")
							$searchEngine = "index, follow";
						$metasXML->appendXMLNode("//sls_configs/action[@id='".$actionId."']", "<robots><![CDATA[".$searchEngine."]]></robots>");
						$controllersXML->appendXMLNode("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers", $xmlAction);
						file_put_contents($this->_generic->getPathConfig("configSecure")."controllers.xml", $controllersXML->getXML());
						file_put_contents($this->_generic->getPathConfig("configSls")."metas.xml", $metasXML->getXML());
						$controllers = $this->_generic->getTranslatedController("SLS_Bo", "Controllers");
						$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller'].".sls");
					}				
				}
				else 
					array_push($errors, "This generic name is already in use for this controller");
								
				if (!empty($errors))
				{
					$xml->startTag("errors");
						foreach ($errors as $error)
							$xml->addFullTag("error", $error, true);
					$xml->endTag("errors");
					$xml->startTag('form');
					$xml->addFullTag("controllerName", $postControllerName);					
					foreach ($postActionsLang as $key=>$value)
						$xml->addFullTag($key."-action", $value, true);					
					$xml->endTag('form');
					$xml->addFullTag("default",(empty($needDefault)) ? "false" : "true",true);
					$xml->addFullTag("dynamic",(empty($needDynamic)) ? "false" : "true",true);
					$xml->addFullTag("offline",(empty($needOffline)) ? "false" : "true",true);
					$xml->startTag("cache");			
						$xml->addFullTag("cache_visibility", $cache_visibility,true);
						$xml->addFullTag("cache_scope", $cache_scope,true);
						$xml->addFullTag("cache_responsive", $cache_responsive,true);
						$xml->addFullTag("cache_expiration", $cache_expiration,true);
					$xml->endTag("cache");
				}
			}
			else
			{
				$defaultExists = $controllersXML->getTag("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@default='1']/@name");
				$xml->addFullTag("default",(empty($defaultExists)) ? "true" : "false",true);
				$xml->startTag("cache");					
					$xml->addFullTag("cache_visibility", "",true);
					$xml->addFullTag("cache_scope", "",true);
					$xml->addFullTag("cache_responsive","",true);
					$xml->addFullTag("cache_expiration", 0,true);
				$xml->endTag("cache");
			}
			
			// Build all tpls
			$tpls = $this->getAppTpls();
			$xml->startTag("tpls");
			foreach($tpls as $template)
				$xml->addFullTag("tpl",$template,true);
			$xml->endTag("tpls");
			
			$xml->startTag('controller');
			$xml->addFullTag("name", $controller, true);
				$xml->startTag('translations');
				foreach ($langs as $lang)
				{
					$xml->startTag('translation');
						$xml->addFullTag("lang", $lang, true);
						$xml->addFullTag("name", $controllersXML->getTag("//controllers/controller[@name='".$controller."' and @side='user']/controllerLangs/controllerLang[@lang='".$lang."']"), true);
					$xml->endTag('translation');
				}
				$xml->endTag('translations');
			$xml->endTag('controller');
			$xml->addFullTag('request', 'addAction', true);
		}
		else {
			$this->_generic->dispatch('SLS_Bo', 'Controllers');
		}
		
		if (empty($tpl))
			$tpl = $controllersXML->getTag("//controllers/controller[@name='".$controller."' and @side='user']/@tpl");
		
		$xml->addFullTag('protocol', $protocol, true);
		$xml->addFullTag('template', $tpl, true);
		
		$aliases = $siteXML->getTagsAttributes("//configs/domainName/domain",array("alias"));
		$xml->startTag("aliases");
		for($i=0 ; $i<$count=count($aliases) ; $i++)
		{
			$xml->startTag("alias");
				$xml->addFullTag("name",$aliases[$i]["attributes"][0]["value"],true);
				$xml->addFullTag("selected",(in_array($aliases[$i]["attributes"][0]["value"],$aliasesSelected)) ? "true" : "false",true);
			$xml->endTag("alias");
		}
		$xml->endTag("aliases");
		
		$components = $this->_generic->recursiveReadDir($this->_generic->getPathConfig("componentsControllers"), array(), array(0=>"php"));
		$xml->startTag("components");				
		foreach ($components as $component)
		{
			$xml->startTag("component");
				$xml->addFullTag("name", SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($component, "/"), ".controller.php"),true);
				$xml->addFullTag("selected",(in_array(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($component, "/"), ".controller.php"),$componentsSelected)) ? "true" : "false",true);
			$xml->endTag("component");
		}
		$xml->endTag("components");
		
		$this->saveXML($xml);
	}
	
}
?>