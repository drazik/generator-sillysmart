<?php
class SLS_BoControllers extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		$listing = true;
		$errors = array();
		
		// Get user controllers listing
		$controllersXML = $this->_generic->getControllersXML();
		$controllers = $controllersXML->getTags("//controllers/controller[@side='user']/@id");		
		$xml->startTag("controllers");
		foreach($controllers as $controller)
		{
			$controller_id = $controller;
			$controller_tpl = $controllersXML->getTag("//controllers/controller[@id='".$controller_id."']/@tpl");
			$controller = $controllersXML->getTag("//controllers/controller[@id='".$controller_id."']/@name");
			$xml->startTag("controller");
			$xml->addFullTag("name",$controller,"true");
			$xml->addFullTag("id",$controller_id,"true");
			$xml->addFullTag("tpl",(!empty($controller_tpl)) ? $controller_tpl : $controller_tpl = "default","true");
			$xml->addFullTag("canBeDeleted",($controller == 'Home' || $controller == 'Default') ? 'false' : 'true', true);
			$scontrollers = $controllersXML->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller/@id");			
			$xml->startTag("scontrollers");
			foreach($scontrollers as $scontroller)
			{
				$scontroller_id = $scontroller;
				$scontroller_tpl = $controllersXML->getTag("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@id='".$scontroller_id."']/@tpl");
				$scontroller_cache = $controllersXML->getTag("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@id='".$scontroller_id."']/@cache");
				if (!SLS_String::contains($scontroller_cache,"|"))
					$scontroller_cache = "false";
				 
				$scontroller = $controllersXML->getTag("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@id='".$scontroller_id."']/@name");				
				$xml->startTag("scontroller");
					$xml->addFullTag("name",$scontroller,true);
					$xml->addFullTag("id",$scontroller_id,true);
					$xml->addFullTag("cache",$scontroller_cache,true);
					$xml->addFullTag("tpl",(!empty($scontroller_tpl)) ? $scontroller_tpl : $controller_tpl,true);
					if (($controller == 'Home' && ($scontroller == 'Index')) || ($controller == 'Default' && ($scontroller == 'UrlError' || $scontroller == 'BadRequestError' || $scontroller == 'TemporaryRedirectError' || $scontroller == 'MaintenanceError' || $scontroller == 'AuthorizationError' || $scontroller == 'ForbiddenError' || $scontroller == 'InternalServerError')))
						$xml->addFullTag("canBeDeleted",'false',true);
					else 
						$xml->addFullTag("canBeDeleted",'true',true);
				$xml->endTag("scontroller");
			}
			$xml->endTag("scontrollers");	
			$xml->endTag("controller");
		}
		$xml->endTag("controllers");
		$xml->startTag("statics");
		$statics = $this->_generic->recursiveReadDir($this->_generic->getPathConfig("staticsControllers"), array(), array(0=>"php"));
		foreach ($statics as $static)
		{			
			$static = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($static, "/"), ".controller.php");
			$cache = $this->_cache->getObject(strtolower($static),"statics","visibility")."|".$this->_cache->getObject(strtolower($static),"statics","scope")."|".$this->_cache->getObject(strtolower($static),"statics","responsive")."|".$this->_cache->getObject(strtolower($static),"statics","expire");
			if ($cache == "|||")
				$cache = "false";
			$xml->startTag("static");
				$xml->addFullTag("id", strtolower($static),true);
				$xml->addFullTag("name", $static,true);
				$xml->addFullTag("cache",$cache,true);
			$xml->endTag("static");
		}
		$xml->endTag("statics");
		$xml->startTag("components");
		$components = $this->_generic->recursiveReadDir($this->_generic->getPathConfig("componentsControllers"), array(), array(0=>"php"));
		foreach ($components as $component)
		{
			$component = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($component, "/"), ".controller.php");
			$cache = $this->_cache->getObject(strtolower($component),"components","visibility")."|".$this->_cache->getObject(strtolower($component),"components","scope")."|".$this->_cache->getObject(strtolower($static),"statics","responsive")."|".$this->_cache->getObject(strtolower($component),"components","expire");
			if ($cache == "|||")
				$cache = "false";
			$xml->startTag("component");
				$xml->addFullTag("id", strtolower($component),true);
				$xml->addFullTag("name", $component,true);
				$xml->addFullTag("cache",$cache,true);
			$xml->endTag("component");
		}
		$xml->endTag("components");
		$xml->addFullTag('request', 'listing', true);
		$this->registerLink('ADDACTION', 'SLS_Bo', 'AddAction', false);
		$this->registerLink('ADDSTATICCONTROLLER', 'SLS_Bo', 'AddStaticController', false);
		$this->registerLink('EDITSTATICCONTROLLER', 'SLS_Bo', 'EditStaticController', false);
		$this->registerLink('DELSTATICCONTROLLER', 'SLS_Bo', 'DeleteStaticController', false);
		$this->registerLink('ADDCOMPONENTCONTROLLER', 'SLS_Bo', 'AddComponentController', false);
		$this->registerLink('EDITCOMPONENTCONTROLLER', 'SLS_Bo', 'EditComponentController', false);
		$this->registerLink('DELCOMPONENTCONTROLLER', 'SLS_Bo', 'DeleteComponentController', false);
		$this->registerLink('EDITACTION', 'SLS_Bo', 'EditAction', false);
		$this->registerLink('DELACTION', 'SLS_Bo', 'DeleteAction', false);
		$this->registerLink('ADDCONTROLLER', 'SLS_Bo', 'AddController', false);
		$this->registerLink('EDITCONTROLLER', 'SLS_Bo', 'EditController', false);
		$this->registerLink('FLUSHCACHE', 'SLS_Bo', 'FlushCache', false);
		$this->registerLink('DELCONTROLLER', 'SLS_Bo', 'DeleteController', false);
		$this->saveXML($xml);		
	}
	
}
?>