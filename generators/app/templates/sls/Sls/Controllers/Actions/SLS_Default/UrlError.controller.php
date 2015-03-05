<?php
class SLS_DefaultUrlError extends SLS_DefaultControllerProtected 
{
	/**
	 * Error 404
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","UrlError"));
		}
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
			header("Status: 404 Not Found");
		}
		
		$http = $this->_generic->getObjectHttpRequest();
		$error = $this->getHTTPErrors();
		$this->_xmlToolBox = new SLS_XMLToolbox(false);		
		$xmlTemp = $this->_xmlToolBox->getXML();
		$this->_xmlToolBox = new SLS_XMLToolbox($this->_xml);
		$this->_xmlToolBox->appendXMLNode("//root", $xmlTemp);
		$this->_xml = $this->_xmlToolBox->getXml();	
	}	
}
?>