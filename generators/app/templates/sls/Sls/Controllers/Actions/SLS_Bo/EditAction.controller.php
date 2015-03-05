<?php
class SLS_BoEditAction extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		$errors = array();
		$siteXML = $this->_generic->getSiteXML();		
		$aliasesSelected = array();
		$componentsSelected = array();
		
		$plugin = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configPlugins")."/plugins.xml"));
		$pluginMobile = $plugin->getTag("//plugins/plugin[@code='mobile']");
		$pluginMobile = (!empty($pluginMobile)) ? true : false;
		
		$controllersXML = $this->_generic->getControllersXML();
		$metasXML = $this->_generic->getCoreXML('metas');
		$controller = $this->_http->getParam('Controller');
		$action = $this->_http->getParam('Action');
				
		$actionExist = $controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']");
		$actionId = array_shift($controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']/@id"));
		$protocol = $this->_generic->getActionProtocol($actionId);
		if (count($actionExist) == 1)
		{
			$aliasesChecked = array_shift($controllersXML->getTagsAttributes("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']",array("domains")));
			if (!empty($aliasesChecked))
			{				
				$alias = explode(",",$aliasesChecked["attributes"][0]["value"]);
				foreach($alias as $cur_alias)
					array_push($aliasesSelected,$cur_alias);
			}
			$componentsChecked = array_shift($controllersXML->getTagsAttributes("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']",array("components")));
			if (!empty($componentsChecked))
			{				
				$component = explode(",",$componentsChecked["attributes"][0]["value"]);
				foreach($component as $cur_component)
					array_push($componentsSelected,$cur_component);
			}
			
			$reload = $this->_http->getParam("reload");
			if ($reload == 'true')
			{
				$oldAction  		= SLS_String::trimSlashesFromString($this->_http->getParam("genericName"));
				$newAction 			= SLS_String::stringToUrl(ucwords(SLS_String::trimSlashesFromString($this->_http->getParam("actionName"))), "", false);
				$needDynamic 		= SLS_String::trimSlashesFromString($this->_http->getParam("dynamic"));
				$needOffline 		= SLS_String::trimSlashesFromString($this->_http->getParam("offline"));
				$needDefault 		= SLS_String::trimSlashesFromString($this->_http->getParam("default"));
				$searchEngine 		= SLS_String::trimSlashesFromString($this->_http->getParam("indexes"));
				$postProtocol		= SLS_String::trimSlashesFromString($this->_http->getParam("protocol"));
				$tpl 				= SLS_String::trimSlashesFromString($this->_http->getParam('template'));
				$aliases			= SLS_String::trimSlashesFromString($this->_http->getParam('domains'));
				$components			= SLS_String::trimSlashesFromString($this->_http->getParam('components'));
				$cache_visibility 	= SLS_String::trimSlashesFromString($this->_http->getParam('cache_visibility'));
				$cache_scope 		= SLS_String::trimSlashesFromString($this->_http->getParam('cache_scope'));
				$cache_expiration 	= SLS_String::trimSlashesFromString($this->_http->getParam('cache_expiration'));
				$cache_responsive 	= SLS_String::trimSlashesFromString($this->_http->getParam('cache_responsive'));
				$toCache			 = (in_array($cache_visibility,array("public","private"))) ? true : false;
				
				if ($controller == "Home" && $action == "Index")
				{
					$needDefault = "true";
					$newAction = $oldAction;
				}
				
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
				
				$postActionsLang	= array();
				$postTitlesLang		= array();
				$postDescriptionsLang = array();
				$postKeywordsLang	= array();
				
				// Save Form informations
				$xml->startTag("form");
					$xml->addFullTag("actionName", $newAction, true);
					foreach ($langs as $lang)
					{
						$postLang = trim(SLS_String::stringToUrl(SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-action")), "", false));
						$postOldLang = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-oldAction"));
						if ($postLang != $oldAction)
						{
							$translationExist = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@name != '".$oldAction."']/scontrollerLangs[scontrollerLang = '".$postLang."']");
						
							if (empty($postLang)) 
								array_push($errors, "You need to fill the ".$lang." url translations");
							elseif(count($translationExist) != 0)
								array_push($errors, "You URL translation in ".$lang." is already in use on another action in the same controller");
							else
								$postActionsLang[$lang] =  $postLang;
						}
						
						// Get Titles	
						$postTitlesLang[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-title"));
						$postDescriptionsLang[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-description"));
						$postKeywordsLang[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-keywords"));
						$xml->addFullTag($lang."-action", $postLang, true);
						$xml->addFullTag($lang."-title", $postTitlesLang[$lang], true);
						$xml->addFullTag($lang."-description", $postDescriptionsLang[$lang], true);
						$xml->addFullTag($lang."-keywords", $postKeywordsLang[$lang], true);
					}
				$xml->endTag("form");
				
				if (empty($postProtocol) || ($postProtocol != 'http' && $postProtocol != 'https'))
						array_push($errors, "Protocol must be http or https");
					else 
						$protocol = $postProtocol;
								
				if (!empty($aliases) && is_array($aliases))
				{
					$aliasesSelected = array();
					foreach($aliases as $alias)
						array_push($aliasesSelected,$alias);
				}
				else
				{
					$aliasesSelected = array();
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']", array('domains' => ''));
				}
				
				if ($toCache && (!is_numeric($cache_expiration) || $cache_expiration < 0))
					array_push($errors, "Your expiration cache must be a positive time or 0");
				if ($toCache && (!in_array($cache_scope,array("full","partial"))))
					array_push($errors, "Your must describe your cache scope");
				
				if ($toCache && is_numeric($cache_expiration) && $cache_expiration >= 0 && in_array($cache_scope,array("full","partial")))
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']", array('cache' => $cache_visibility.'|'.$cache_scope.'|'.(($cache_responsive == 'true') ? 'responsive' : 'no_responsive').'|'.$cache_expiration));
				if (!$toCache)
					$controllersXML->deleteTagAttribute("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']","cache");
				
				if (!empty($components) && is_array($components))
				{
					$componentsSelected = array();
					foreach($components as $component)
						array_push($componentsSelected,$component);
				}
				else
				{
					$componentsSelected = array();
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']", array('components' => ''));
				}
				
				if ((($controller != 'Home' && $controller != 'Default') || (($controller == 'Home' && $action != 'Index') || ($controller == 'Default' && ($action != 'UrlError' && $action != 'BadRequestError' && $action != 'AuthorizationError' && $action != 'ForbiddenError' && $action != 'InternalServerError' && $action != 'TemporaryRedirectError' && $action != 'MaintenanceError')))) && ($oldAction != $newAction)) 
				{
					$newNameExist = $controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$newAction."']");
					if (count($newNameExist) != 0)
						array_push($errors, "The generic action name is already in use in this controller");						
				}
				else 
					$newAction = $oldAction;
				
				if (empty($newAction))
					array_push($errors, "Action name can't be empty.");
					
				if (!empty($aliases))									
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']", array('domains' => implode(",",$aliasesSelected)));					
					
				if (!empty($components))									
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']", array('components' => implode(",",$componentsSelected)));
								
				$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."' and @id='".$actionId."']", array('disable' => (empty($needOffline)) ? '0' : '1'));					
									
				if ($tpl == -1)
					$controllersXML->deleteTagAttribute("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']", "tpl");
				else
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']", array('tpl' => $tpl));
				file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());

				$dynamic = (($controller != 'Home' && $controller != 'Default') || (($controller == 'Home' && $action != 'Index') || ($controller == 'Default' && ($action != 'UrlError' && $action != 'BadRequestError' && $action != 'AuthorizationError' && $action != 'ForbiddenError' && $action != 'InternalServerError' && $action != 'TemporaryRedirectError' && $action != 'MaintenanceError')))) ? ($needDynamic == 'on') ? "1" : "0" : array_shift($controllersXML->getTags("//controllers/controller[@name='".$controller."' and side='user']/scontrollers/scontroller[@name='".$oldAction."']/@needParam"));
				// If no errors
				if (empty($errors))
				{
					// If default, reset other in current controller
					if (!empty($needDefault))
					{
						$actions = $controllersXML->getTags("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller/@id");
						foreach($actions as $curActionId)
							$controllersXML->setTagAttributes("//controllers/controller[@side='user' and @name='".$controller."']/scontrollers/scontroller[@id='".$curActionId."']", array('default' => '0'));
					}
					
					$controllersXML->setTagAttributes("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']", array("name"=>$newAction,"needParam"=>$dynamic,'protocol'=>$protocol,"default"=>(empty($needDefault) ? '0' : '1')));
					
					foreach ($langs as $lang)
					{
						if (array_key_exists($lang, $postActionsLang))
							$controllersXML->setTag("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']", $postActionsLang[$lang], true);
						else 
							$controllersXML->setTag("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']", SLS_String::trimSlashesFromString($this->_http->getParam(strtolower($lang)."-oldAction")), true);
					}
					
					// If generic name is not the same, we modify all files
					if ($oldAction != $newAction)
					{
						// Controller
						$contentController = file_get_contents($this->_generic->getPathConfig('actionsControllers').$controller."/".$oldAction.".controller.php");
						$contentController = str_replace(array(0=>" ".$controller.$oldAction." ", 1=>" ".$oldAction." "), array(0=>" ".$controller.$newAction." ", 1=>" ".$newAction." "), $contentController);
						file_put_contents($this->_generic->getPathConfig('actionsControllers').$controller."/".$newAction.".controller.php", $contentController);
						unlink($this->_generic->getPathConfig('actionsControllers').$controller."/".$oldAction.".controller.php");
						
						//Langs
						foreach ($langs as $lang)
						{
							$contentLang = file_get_contents($this->_generic->getPathConfig('actionLangs').$controller."/".$oldAction.".".$lang.".lang.php");
							$contentLang = str_replace(array(0=>" ".$controller.$oldAction." ", 1=>" ".$oldAction." "), array(0=>" ".$controller.$newAction." ", 1=>" ".$newAction." "), $contentLang);
							file_put_contents($this->_generic->getPathConfig('actionLangs').$controller."/".$newAction.".".$lang.".lang.php", $contentLang);
							unlink($this->_generic->getPathConfig('actionLangs').$controller."/".$oldAction.".".$lang.".lang.php");
						}
						
						// Views
						// Body
						$contentBody = file_get_contents($this->_generic->getPathConfig('viewsBody').$controller."/".$oldAction.".xsl");
						$contentBody = str_replace(array(0=>"name=\"".$oldAction."\">", 1=>$oldAction.".xsl"), array(0=>"name=\"".$newAction."\">", 1=>$newAction.".xsl"), $contentBody);
						file_put_contents($this->_generic->getPathConfig('viewsBody').$controller."/".$newAction.".xsl", $contentBody);
						unlink($this->_generic->getPathConfig('viewsBody').$controller."/".$oldAction.".xsl");
						
						// Headers
						$contentHeader = file_get_contents($this->_generic->getPathConfig('viewsHeaders').$controller."/".$oldAction.".xsl");
						$contentHeader = str_replace(array(0=>"name=\"Header".$oldAction."\">"), array(0=>"name=\"Header".$newAction."\">"), $contentHeader);
						file_put_contents($this->_generic->getPathConfig('viewsHeaders').$controller."/".$newAction.".xsl", $contentHeader);
						unlink($this->_generic->getPathConfig('viewsHeaders').$controller."/".$oldAction.".xsl");
					}
					
					// We now update the XML
					
					foreach ($langs as $lang)
					{
						// Metas
						$metasXML->setTag("//sls_configs/action[@id='".$actionId."']/title[@lang='".$lang."']", $postTitlesLang[$lang], true);
						$metasXML->setTag("//sls_configs/action[@id='".$actionId."']/description[@lang='".$lang."']", $postDescriptionsLang[$lang], true);
						$metasXML->setTag("//sls_configs/action[@id='".$actionId."']/keywords[@lang='".$lang."']", $postKeywordsLang[$lang], true);
					}
					if (!SLS_String::contains($searchEngine,", "))
						$searchEngine = str_replace(",",", ",$searchEngine);
					if ($searchEngine != "index, follow" && $searchEngine != "noindex, follow" && $searchEngine != "noindex, nofollow" && $searchEngine != "index, nofollow")
						$searchEngine = "index, follow";
					$metasXML->setTag("//sls_configs/action[@id='".$actionId."']/robots", $searchEngine, true);
							
					file_put_contents($this->_generic->getPathConfig("configSecure")."controllers.xml", $controllersXML->getXML());
					file_put_contents($this->_generic->getPathConfig("configSls")."metas.xml", $metasXML->getXML());					
				}
				
				if (count($errors) != 0)
				{
					$xml->startTag("errors");
					foreach ($errors as $error)
						$xml->addFullTag("error", $error, true);
					$xml->endTag("errors");
				}
				
				$xml->startTag("cache");					
					$xml->addFullTag("cache_visibility", $cache_visibility,true);
					$xml->addFullTag("cache_scope", $cache_scope,true);
					$xml->addFullTag("cache_expiration", $cache_expiration,true);
					$xml->addFullTag("cache_responsive", $cache_responsive,true);
				$xml->endTag("cache");
			}
			else
			{
				$actionCache = $this->_cache->getAction($actionId);				
				$xml->startTag("cache");					
					$xml->addFullTag("cache_visibility", (is_array($actionCache)) ? $actionCache[0] : "",true);
					$xml->addFullTag("cache_scope", (is_array($actionCache)) ? $actionCache[1] : "",true);
					$xml->addFullTag("cache_expiration", (is_array($actionCache)) ? $actionCache[3] : "",true);
					$xml->addFullTag("cache_responsive", (is_array($actionCache)) ? (($actionCache[2]=="responsive") ? "true" : "") : "",true);
				$xml->endTag("cache");
			}
			$tpl = $controllersXML->getTag("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']/@tpl");
			if (empty($tpl))
				$tpl = $controllersXML->getTag("//controllers/controller[@name='".$controller."' and @side='user']/@tpl");
			
			$controllersXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSecure")."controllers.xml"));
			
			$xml->startTag("action");
				$xml->addFullTag("name", $action, true);
				$xml->addFullTag("dynamic", (array_shift($controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']/@needParam")) == '1') ? 'true' : 'false', true);
				$xml->addFullTag("offline", (array_shift($controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']/@disable")) == '1') ? 'true' : 'false', true);
				$xml->addFullTag("default", (array_shift($controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']/@default")) == '1') ? 'true' : 'false', true);
				$xml->addFullTag("indexes", array_shift($metasXML->getTags("//sls_configs/action[@id='".$actionId."']/robots")), true);
				$xml->addFullTag("canBeModified", (($controller != 'Home' && $controller != 'Default') || (($controller == 'Home' && $action != 'Index') || ($controller == 'Default' && ($action != 'UrlError' && $action != 'BadRequestError' && $action != 'AuthorizationError' && $action != 'ForbiddenError' && $action != 'InternalServerError' && $action != 'TemporaryRedirectError' && $action != 'MaintenanceError')))) ? 'true' : 'false', true);
				$xml->startTag("translations");
				foreach ($langs as $lang)
				{
					$xml->startTag("translation");
					$xml->addFullTag("lang", $lang, true);
					$xml->addFullTag("name", array_shift($controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$action."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']")) ,true);
					$xml->addFullTag("title", array_shift($metasXML->getTags("//sls_configs/action[@id='".$actionId."']/title[@lang='".$lang."']")),true);
					$xml->addFullTag("description", array_shift($metasXML->getTags("//sls_configs/action[@id='".$actionId."']/description[@lang='".$lang."']")),true);
					$xml->addFullTag("keywords", array_shift($metasXML->getTags("//sls_configs/action[@id='".$actionId."']/keywords[@lang='".$lang."']")),true);
					$xml->endTag("translation");
				}
				$xml->endTag("translations");
			$xml->endTag("action");
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
			
			// Build all tpls
			$tpls = $this->getAppTpls();
			$xml->startTag("tpls");
			foreach($tpls as $template)
				$xml->addFullTag("tpl",$template,true);
			$xml->endTag("tpls");
			
			$xml->addFullTag('request', 'modifyAction', true);			
			
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
		}
		else 
		{
			$this->_generic->dispatch('SLS_Bo', 'Controllers');
		}
		$xml->addFullTag('protocol', $protocol, true);
		$xml->addFullTag('template', $tpl, true);
		$this->saveXML($xml);
	}
	
}
?>