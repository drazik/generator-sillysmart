<?php
/**
 * Action UrlError Controller Default
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.Default.ControllerProtected
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultUrlError extends DefaultControllerProtected 
{
	public function init()
	{
		parent::init();
	}
	
	/**
	 * Error 404
	 *
	 * @access public
	 */
	public function action() 
	{
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
		header("Status: 404 Not Found");
		
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