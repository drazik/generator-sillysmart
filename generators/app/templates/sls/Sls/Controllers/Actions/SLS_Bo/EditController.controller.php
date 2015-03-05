<?php
class SLS_BoEditController extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		$errors = array();
		$controllersXML = $this->_generic->getControllersXML();
		$controller = $this->_http->getParam('Controller');
		$protocol = $this->_generic->getSiteConfig("protocol");
		
		$controllerExist = $controllersXML->getTags("//controllers/controller[@name='".$controller."' and @side='user']");
		if (count($controllerExist) == 1)
		{
			if ($this->_http->getParam('reload') == 'true')
			{
				$postControllerName = SLS_String::trimSlashesFromString($this->_http->getParam('controllerName'));
				$controller 		= SLS_String::trimSlashesFromString($this->_http->getParam('oldName'));
				$postProtocol 		= SLS_String::trimSlashesFromString($this->_http->getParam('protocol'));
				$tpl 				= SLS_String::trimSlashesFromString($this->_http->getParam('template'));
				
				if (empty($postProtocol) || ($postProtocol != 'http' && $postProtocol != 'https'))
					array_push($errors, "Protocol must be http or https");
				else 
					$protocol = $postProtocol;
				
				$postControllerLangs = array();
				foreach ($langs as $lang)
					$postControllerLangs[$lang] = SLS_String::stringToUrl(SLS_String::trimSlashesFromString($this->_http->getParam($lang."-controller")), "", false);
				
				if ($tpl == -1)
					$controllersXML->deleteTagAttribute("//controllers/controller[@name='".$controller."' and @side='user']", "tpl");
				else
					$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."' and @side='user']", array('tpl' => $tpl));
				@file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());
								
				$oldControllerName = $controller;
				if (empty($postControllerName))
					array_push($errors, "Controller name can't be empty.");
				if (!empty($postControllerName) && $postControllerName != $controller)
				{
					$newControllerName = SLS_String::stringToUrl($postControllerName, "", false);
					$test = $controllersXML->getTags("//controllers/controller[@name='".$newControllerName."']");
					
					if (count($test) != 0)
						array_push($errors, ($newControllerName == $postControllerName) ? "The controller Name '".$postControllerName."' already exist" : "The controller Name '".$postControllerName."' (".$newControllerName.") already exist");
					else 
					{
						$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."' and @side='user']", array('name' => $newControllerName));
						@file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());
						$controller = $newControllerName;
						
						// Controller Files
						$files = scandir($this->_generic->getPathConfig('actionsControllers').$oldControllerName);						
						foreach ($files as $file)
							if (is_file($this->_generic->getPathConfig('actionsControllers').$oldControllerName."/".$file) && substr($file, strlen($file)-3) == "php")
								@file_put_contents($this->_generic->getPathConfig('actionsControllers').$oldControllerName."/".$file, str_replace(array(0=>" ".$oldControllerName,1=>".".$oldControllerName), array(0=>" ".$newControllerName,1=>".".$newControllerName), file_get_contents($this->_generic->getPathConfig('actionsControllers').$oldControllerName."/".$file)));
							
						//Langs
						$files = scandir($this->_generic->getPathConfig('actionLangs').$oldControllerName);
						foreach ($files as $file)						
							if (is_file($this->_generic->getPathConfig('actionLangs').$oldControllerName."/".$file) && substr($file, strlen($file)-3) == "php")							
								@file_put_contents($this->_generic->getPathConfig('actionLangs').$oldControllerName."/".$file, str_replace(array(0=>" ".$oldControllerName,1=>".".$oldControllerName), array(0=>" ".$newControllerName,1=>".".$newControllerName), file_get_contents($this->_generic->getPathConfig('actionLangs').$oldControllerName."/".$file)));
						
						// Rename Directories						
						@rename($this->_generic->getPathConfig('actionsControllers').$oldControllerName, $this->_generic->getPathConfig('actionsControllers').$controller);
						foreach ($langs as $lang)									
							@rename($this->_generic->getPathConfig('actionLangs').$oldControllerName."/__".$oldControllerName.".".strtolower($lang).".lang.php", $this->_generic->getPathConfig('actionLangs').$oldControllerName."/__".$controller.".".strtolower($lang).".lang.php");
												
						rename($this->_generic->getPathConfig('actionLangs').$oldControllerName, $this->_generic->getPathConfig('actionLangs').$controller);						
						rename($this->_generic->getPathConfig('viewsBody').$oldControllerName, $this->_generic->getPathConfig('viewsBody').$controller);						
						rename($this->_generic->getPathConfig('viewsHeaders').$oldControllerName, $this->_generic->getPathConfig('viewsHeaders').$controller);
					}
				}
				if (empty($errors))
				{
					$translatedController = $controllersXML->getTags("//controllers/controller[@name != '".$controller."']/controllerLangs/controllerLang",  'id');
					foreach ($postControllerLangs as $key=>$value)
					{
						if (in_array($value, $translatedController))
							array_push($errors, "The translated name '".$value."' for this controller is alredy used");
					}
					if (empty($errors))
					{
						foreach ($postControllerLangs as $key=>$value)
						{
							$controllersXML->setTag("//controllers/controller[@name = '".$controller."' and @side='user']/controllerLangs/controllerLang[@lang='".$key."']", $postControllerLangs[$key], true);
						}
						$controllersXML->setTagAttributes("//controllers/controller[@name='".$controller."' and @side='user']", array('protocol' => $protocol));
						file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());
					}
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
					foreach ($postControllerLangs as $key=>$value)
						$xml->addFullTag($key."-controller", $value, true);						
				$xml->endTag('form');
			}
			
			$tplResult = array_shift($controllersXML->getTagsAttribute("//controllers/controller[@name='".$controller."' and @side='user']","tpl"));
			$tpl = $tplResult["attribute"];
			
			$xml->startTag('controller');
				$xml->addFullTag("name", $controller, true);
				$xml->addFullTag("tpl", $tpl, true);
				$xml->addFullTag("locked", ($controller == 'Home' || $controller == 'Default') ? 1 : 0, true);
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
			$xml->addFullTag('request', 'modifyController', true);
			
			// Build all tpls
			$tpls = $this->getAppTpls();
			$xml->startTag("tpls");
			foreach($tpls as $template)
				$xml->addFullTag("tpl",$template,true);
			$xml->endTag("tpls");
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