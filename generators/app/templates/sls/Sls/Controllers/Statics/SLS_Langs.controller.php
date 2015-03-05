<?php
/**
 * Controller SLS_Langs - Specific controller of Langs
 *  
 * @author Florian Collot
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @since 1.0 
 */
class SLS_LangsController extends SLS_FrontStatic implements SLS_IStatic 
{
	private $_cur_lang;	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Construct the XML
	 *
	 * @access public
	 * @since 1.0
	 */
	public function constructXML()
	{
		$this->_cur_lang = $this->_generic->getObjectLang();
		$this->_xmlToolBox->startTag("Langs");
		$runningController = $this->_generic->getGenericControllerName();
		$runningScontroller = $this->_generic->getGenericScontrollerName();
		$keyProject = strtoupper($this->_generic->getSiteConfig("projectName"));
		
		if (!empty($GLOBALS[$keyProject]))
		{
			$this->_xmlToolBox->startTag("xsl");
			/*
			if (!empty($GLOBALS[$keyProject]['XSL']))
			{
				foreach($GLOBALS[$keyProject]['XSL'] as $key => $value)
				{
					$this->_xmlToolBox->startTag("sentence");					
					$this->_xmlToolBox->addFullTag("name",strtoupper($key), true);
					$this->_xmlToolBox->addFullTag("value",$value, true);
					$this->_xmlToolBox->endTag("sentence");			
				}				
			}*/
			$this->_xmlToolBox->endTag("xsl");
			$this->_xmlToolBox->startTag("js");
			if (!empty($GLOBALS[$keyProject]['JS']))
			{
				foreach($GLOBALS[$keyProject]['JS'] as $key => $value)
				{
					$this->_xmlToolBox->startTag("sentence");					
					$this->_xmlToolBox->addFullTag("name",strtoupper($key), true);
					$this->_xmlToolBox->addFullTag("value",$value, true);
					$this->_xmlToolBox->endTag("sentence");			
				}
			}
			$this->_xmlToolBox->endTag("js");
		}	
		$this->_xmlToolBox->endTag("Langs");		
	}
}
?>