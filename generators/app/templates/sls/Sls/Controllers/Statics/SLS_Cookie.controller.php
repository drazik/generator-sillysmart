<?php
/**
 * Controller SLS_Cookie - Specific controller of Cookies
 *
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @since 1.0.8
 */
class SLS_CookieController extends SLS_FrontStatic implements SLS_IStatic 
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0.8
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Construct the XML
	 *
	 * @access public
	 * @since 1.0.8
	 */
	public function constructXML()
	{
		$this->_xmlToolBox->startTag("Cookie");
		
		$cookies = $_COOKIE;		
		foreach($cookies as $name => $content)
		{			
			$cookie = new SLS_Cookie($name);
			$cookieParams = $cookie->getParams();
			if (!empty($cookieParams))
			{
				$this->_xmlToolBox->startTag("item",array("name" => $name));
					$this->_xmlToolBox->startTag("params");
					foreach($cookieParams as $key => $value)
					{
						if (empty($value))
							$value = "null";
						if (is_object($value))
							$value = SLS_String::printArray(SLS_String::objectToArray($value));
						else if (is_array($value))
							$value = SLS_String::printArray($value);
						
						$this->_xmlToolBox->startTag("param");
							$this->_xmlToolBox->addFullTag("name", $key, true);
							$this->_xmlToolBox->addFullTag("value", $value, true);
						$this->_xmlToolBox->endTag("param");
					}
					$this->_xmlToolBox->endTag("params");
				$this->_xmlToolBox->endTag("item");
			}
		}
		
		$this->_xmlToolBox->endTag("Cookie");
	}	
}
?>