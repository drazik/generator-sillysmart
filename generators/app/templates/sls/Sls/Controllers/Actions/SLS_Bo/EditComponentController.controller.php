<?php
class SLS_BoEditComponentController extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$errors = array();
		
		$plugin = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configPlugins")."/plugins.xml"));
		$pluginMobile = $plugin->getTag("//plugins/plugin[@code='mobile']");
		$pluginMobile = (!empty($pluginMobile)) ? true : false;
		
		$controller = SLS_String::trimSlashesFromString($this->_http->getParam('Controller'));
				
		if (is_file($this->_generic->getPathConfig("componentsControllers").$controller.".controller.php"))
		{
			if ($this->_http->getParam('reload') == 'true')
			{
				$newController = SLS_String::trimSlashesFromString($this->_http->getParam('controllerName'));
				$oldController = SLS_String::trimSlashesFromString($this->_http->getParam('oldName'));
				$cache_visibility = SLS_String::trimSlashesFromString($this->_http->getParam('cache_visibility'));
				$cache_expiration = SLS_String::trimSlashesFromString($this->_http->getParam('cache_expiration'));
				$cache_responsive = SLS_String::trimSlashesFromString($this->_http->getParam('cache_responsive'));
				$toCache = (in_array($cache_visibility,array("public","private"))) ? true : false;
				
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
				
				if (empty($newController))
					array_push($errors, "You must fill the component controller name");
				if (in_array($cache_visibility,array("public","private")) && (!is_numeric($cache_expiration) || $cache_expiration < 0))
					array_push($errors, "Your expiration cache must be a positive time or 0");
				
				$newController = str_replace(" ", "", ucwords(trim(SLS_String::getAlphaString($newController))));
					
				$components = $this->_generic->recursiveReadDir($this->_generic->getPathConfig("componentsControllers"), array(), array(0=>"php"));
				foreach ($components as $component)
				{
					if (SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterLastDelimiter($component, "/"), ".controller.php") == $newController && SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterLastDelimiter($component, "/"), ".controller.php") != $oldController)
					{
						array_push($errors, "The name '".$newController."' is already in use for a component controller");
						break;
					}
				}
				
				if (empty($errors))
				{
					if ($newController != $oldController)
					{
						$strController = file_get_contents($this->_generic->getPathConfig("componentsControllers").$oldController.".controller.php");
						$strController = str_replace($oldController."Controller", $newController."Controller", $strController);
						file_put_contents($this->_generic->getPathConfig("componentsControllers").$newController.".controller.php", $strController);
						unlink($this->_generic->getPathConfig("componentsControllers").$oldController.".controller.php");
					}
					
					// Cache
					if (in_array($cache_visibility,array("public","private")))
					{
						$this->_cache->addObject(strtolower($newController),"components",$cache_visibility,($cache_responsive=="true") ? "responsive" : "no_responsive",$cache_expiration);
						$this->_cache->saveObject();
					}
					else
					{
						$this->_cache->deleteObject(strtolower($newController),"components");
						$this->_cache->saveObject();
					}
					
					$this->_generic->forward("SLS_Bo", "Controllers");
				}		
				if (!empty($errors))
				{
					$xml->startTag("errors");
						foreach ($errors as $error)
							$xml->addFullTag("error", $error, true);
					$xml->endTag("errors");
				}
				$xml->startTag('form');
					$xml->addFullTag("controllerName", $postControllerName);
					$xml->addFullTag("cache_visibility", $cache_visibility,true);
					$xml->addFullTag("cache_expiration", $cache_expiration,true);
					$xml->addFullTag("cache_responsive", $cache_expiration,true);		
				$xml->endTag('form');
			}
			else
			{
				$xml->startTag('form');
					$xml->addFullTag("controllerName", $controller);
					$xml->addFullTag("cache_visibility", $this->_cache->getObject(strtolower($controller),"components","visibility"),true);
					$xml->addFullTag("cache_expiration", $this->_cache->getObject(strtolower($controller),"components","expire"),true);
					$xml->addFullTag("cache_responsive", $this->_cache->getObject(strtolower($controller),"components","responsive"),true);
				$xml->endTag('form');
			}
			
			$xml->startTag('controller');
				$xml->addFullTag("name", $controller, true);
			$xml->endTag('controller');
		}
		else 
		{
			$this->_generic->forward('SLS_Bo', 'Controllers');
		}
		
		$this->saveXML($xml);
	}
	
}
?>