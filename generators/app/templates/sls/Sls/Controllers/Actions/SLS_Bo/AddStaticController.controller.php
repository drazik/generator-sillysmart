<?php
class SLS_BoAddStaticController extends SLS_BoControllerProtected 
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
		
		if ($this->_http->getParam('reload') == 'true')
		{
			$controller = SLS_String::trimSlashesFromString($this->_http->getParam('controllerName'));
			$cache_visibility = SLS_String::trimSlashesFromString($this->_http->getParam('cache_visibility'));
			$cache_expiration = SLS_String::trimSlashesFromString($this->_http->getParam('cache_expiration'));
			$cache_responsive = SLS_String::trimSlashesFromString($this->_http->getParam('cache_responsive'));
			
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
			
			if (empty($controller))
				array_push($errors, "You must fill the static controller name");
			if (in_array($cache_visibility,array("public","private")) && (!is_numeric($cache_expiration) || $cache_expiration < 0))
				array_push($errors, "Your expiration cache must be a positive time or 0");
			
			$controller = str_replace(" ", "", ucwords(trim(SLS_String::getAlphaString($controller))));
			
			$statics = $this->_generic->recursiveReadDir($this->_generic->getPathConfig("staticsControllers"), array(), array(0=>"php"));
			foreach ($statics as $static)
			{
				if (SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterLastDelimiter($static, "/"), ".controller.php") == $controller)
				{
					array_push($errors, "The name '".$controller."' is already in use for a static controller");
					break;
				}
			}
			
			if (empty($errors))
			{
				$strNewStatic = '<?php'."\n".
						   '/**'."\n".
						   '* Controller Static '.$controller.'Controller'."\n".
						   '*'."\n".
						   '* @author SillySmart'."\n".
						   '* @copyright SillySmart'."\n".
						   '* @package Mvc.Controllers.Statics.'.$controller.'Controller'."\n".
						   '* @see Sls.Controllers.Core.SLS_FrontStatic'."\n".
						   '* @since 1.0'."\n".
						   '*/'."\n".
						   'class '.$controller.'Controller extends SLS_FrontStatic implements SLS_IStatic '."\n".
						   '{'."\n\n".						   
						   t(1).'public function __construct()'."\n".
						   t(1).'{'."\n".
						   		t(2).'parent::__construct(true);'."\n".
						   t(1).'}'."\n\n".
						   t(1).'public function constructXML()'."\n".
						   t(1).'{'."\n".
						   		t(2).'// Write here all your instructions to make your Static configuration with xml by $this->_xmlToolBox'."\n".
						   t(1).'}'."\n\n".						   
						   '}'."\n".
						   '?>'; 
				file_put_contents($this->_generic->getPathConfig("staticsControllers").$controller.".controller.php", $strNewStatic);

				// Cache
				if (in_array($cache_visibility,array("public","private")))
				{
					$this->_cache->addObject(strtolower($controller),"statics",$cache_visibility,($cache_responsive=="true") ? "responsive" : "no_responsive",$cache_expiration);
					$this->_cache->saveObject();
				}
				
				$this->_generic->forward('SLS_Bo', 'Controllers');
			}
			
			if (!empty($errors))
			{
				$xml->startTag("errors");
					foreach ($errors as $error)
						$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
				$xml->startTag('form');
					$xml->addFullTag("controllerName", $controller,true);
					$xml->addFullTag("cache_visibility", $cache_visibility,true);
					$xml->addFullTag("cache_expiration", $cache_expiration,true);
					$xml->addFullTag("cache_responsive", $cache_responsive,true);												
				$xml->endTag('form');
			}
		}
		else
		{
			$xml->startTag('form');
				$xml->addFullTag("controllerName", $controller,true);
				$xml->addFullTag("cache_visibility", "",true);
				$xml->addFullTag("cache_expiration", "0",true);
				$xml->addFullTag("cache_responsive", "",true);		
			$xml->endTag('form');
		}
		
		$this->saveXML($xml);
	}
}
?>