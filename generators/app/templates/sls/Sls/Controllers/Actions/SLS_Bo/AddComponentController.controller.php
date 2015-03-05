<?php
class SLS_BoAddComponentController extends SLS_BoControllerProtected 
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
				array_push($errors, "You must fill the component controller name");
			if (in_array($cache_visibility,array("public","private")) && (!is_numeric($cache_expiration) || $cache_expiration < 0))
				array_push($errors, "Your expiration cache must be a positive time or 0");
			
			$controller = str_replace(" ", "", ucwords(trim(SLS_String::getAlphaString($controller))));
			
			$components = $this->_generic->recursiveReadDir($this->_generic->getPathConfig("componentsControllers"), array(), array(0=>"php"));
			foreach ($components as $component)
			{
				if (SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterLastDelimiter($component, "/"), ".controller.php") == $controller)
				{
					array_push($errors, "The name '".$controller."' is already in use for a component controller");
					break;
				}
			}
			
			if (empty($errors))
			{
				$strNewComponent = '<?php'."\n".
						   '/**'."\n".
						   '* Controller Component '.$controller.'Controller'."\n".
						   '*'."\n".
						   '* @author SillySmart'."\n".
						   '* @copyright SillySmart'."\n".
						   '* @package Mvc.Controllers.Components.'.$controller.'Controller'."\n".
						   '* @see Sls.Controllers.Core.SLS_FrontComponent'."\n".
						   '* @since 1.0'."\n".
						   '*/'."\n".
						   'class '.$controller.'Controller extends SLS_FrontComponent implements SLS_IComponent '."\n".
						   '{'."\n".
						   ''."\n".
						   t(1).'public function __construct()'."\n".
						   t(1).'{'."\n".
						   		t(2).'parent::__construct(true);'."\n".
						   t(1).'}'."\n".
						   ''."\n".
						   t(1).'public function constructXML()'."\n".
						   t(1).'{'."\n".
						   		t(2).'// Write here all your instructions to make your Component configuration with xml by $this->_xmlToolBox'."\n".
						   t(1).'}'."\n".
						   ''."\n".
						   '}'."\n".
						   '?>'; 
				file_put_contents($this->_generic->getPathConfig("componentsControllers").$controller.".controller.php", $strNewComponent);

				// Cache
				if (in_array($cache_visibility,array("public","private")))
				{
					$this->_cache->addObject(strtolower($controller),"components",$cache_visibility,($cache_responsive=="true") ? "responsive" : "no_responsive",$cache_expiration);
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
					$xml->addFullTag("controllerName", $controller);
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