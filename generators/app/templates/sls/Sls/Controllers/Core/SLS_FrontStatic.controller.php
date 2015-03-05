<?php
/**
 * Interface to implement foreach Statics Controllers
 *
 * @author Florian Collot 
 * @copyright SillySmart
 * @package Sls.Controllers.Core 
 * @since 1.0
 */
interface SLS_IStatic 
{
	public function constructXML();
	public function getXML();
}

/**
 * Controller FrontStatic - Father's controller of each Statics Controllers
 *
 * @author Florian Collot
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Core 
 * @since 1.0
 */
class SLS_FrontStatic 
{
	protected $_generic;
	protected $_xmlToolBox;
	protected $_http;
	protected $_session;
	protected $_lang;
	protected $_security;
	protected $_onSide = 'sls';
		
	/**
	 * Constructor
	 *
	 * @access public
	 * @param bool $userSide true if it's user side, else false
	 * @since 1.0
	 */
	public function __construct($userSide=false) 
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_http = $this->_generic->getObjectHttpRequest();
		$this->_session = $this->_generic->getObjectSession();		
		$this->_lang = $this->_generic->getObjectLang();
		$this->_security = $this->_generic->getObjectSecurity();
		$this->_xmlToolBox = new SLS_XMLToolbox(false);
		if ($userSide === true)
		{
			$this->_onSide = 'user';
			$this->_xmlToolBox->startTag(SLS_String::substrBeforeLastDelimiter(get_class($this), "Controller"));			
		}
		$this->constructXML();
	}
	
	/**
	 * Get the XML of the static controller
	 *
	 * @access public
	 * @return string $xml the xml
	 * @since 1.0
	 */
	public function getXML()
	{
		if ($this->_onSide == 'user')
		{
			$this->_xmlToolBox->endTag(SLS_String::substrBeforeLastDelimiter(get_class($this), "Controller"));
		}
		return $this->_xmlToolBox->getXML('noHeader');
	}	
}
?>