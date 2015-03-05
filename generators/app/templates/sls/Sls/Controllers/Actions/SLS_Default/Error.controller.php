<?php
class SLS_DefaultError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error of action for an existing controller
	 *
	 * @access public
	 * @param string $controller the current controller
	 */
	public function action($controller)
	{
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
		header("Status: 404 Not Found");
		$this->setMetaRobots("noindex, follow");
		
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","Error"));
		}
		
		// We recover actions of the same controller who doesn't need a dynamic param
		$actions = $this->_generic->getActionsNoParams($controller);
		$this->setMetaTitle("Error - Unknown Action | ".ucwords($this->_generic->getSiteConfig("projectName")));
		$this->_xmlToolBox = new SLS_XMLToolbox(false);		
		$this->_xmlToolBox->startTag("actions");		
		foreach($actions as $value)
		{
			$currentPage = array_shift($this->_generic->getControllersXML()->getTagsAttribute("//controllers/controller[@name='".$controller."' and @side='user']/scontrollers/scontroller[@name='".$value."']/scontrollerLangs/scontrollerLang[@lang='".$this->_generic->getObjectLang()->getLang()."']","title"));			
			$controllers = $this->_generic->getTranslatedController($controller,$value);
			$href = $controllers['protocol']."://".$this->_generic->getSiteConfig("domainName")."/".$controllers['controller']."/".$controllers['scontroller'];
			if ($this->_generic->getSiteConfig("defaultExtension") != "")
				$href .= ".".$this->_generic->getSiteConfig("defaultExtension");
			$this->_xmlToolBox->startTag("action");
			$this->_xmlToolBox->addFullTag("label",(empty($currentPage["attribute"])) ? $value : $currentPage["attribute"],true);
			$this->_xmlToolBox->addFullTag("href",$href,true);
			$this->_xmlToolBox->endTag("action");
		}
		$this->_xmlToolBox->endTag("actions");		
		$xmlTmp = $this->_xmlToolBox->getXML();	
		$this->_xmlToolBox = new SLS_XMLToolbox($this->_xml);
		$this->_xmlToolBox->appendXMLNode("//root",$xmlTmp);
		$this->_xml = $this->_xmlToolBox->getXML();		
	}	
}
?>