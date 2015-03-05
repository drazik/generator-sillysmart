<?php
/**
 * Controller SLS_Session - Specific controller of Session
 *
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @since 1.0 
 */
class SLS_SessionController extends SLS_FrontStatic implements SLS_IStatic 
{
	private $_cur_session;
	
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
		$this->_xmlToolBox->startTag("Session");
		
		$params = $this->_session->getParams();		
		uksort($params,"strnatcasecmp");
		$this->_xmlToolBox->startTag("params");
		foreach($params as $key => $value)
		{
			$type = "string";

			if (empty($value))
				$value = "null";
			if (is_object($value))
			{
				$value = str_replace("\t","    ", SLS_String::printObject(json_encode($value)));
				$type = "object";
			}
			else if (is_array($value))
			{
				$value = str_replace("\t","    ", SLS_String::printArray($value));
				$type = "array";
			}
			$this->_xmlToolBox->startTag("param", array("type" => $type));
			$this->_xmlToolBox->addFullTag("name",$key,true);
			$this->_xmlToolBox->addFullTag("value",$value,true);
			$this->_xmlToolBox->endTag("param");
		}
		$this->_xmlToolBox->endTag("params");
		
		$this->_xmlToolBox->endTag("Session");
	}	
}
?>