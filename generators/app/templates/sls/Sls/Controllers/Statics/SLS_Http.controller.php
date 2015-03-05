<?php
/**
 * Controller SLS_Http - Specific controller of HTTP's params
 *
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @since 1.0 
 */
class SLS_HttpController extends SLS_FrontStatic implements SLS_IStatic 
{	
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
		$this->_xmlToolBox->startTag("Http");
		
		$methods = array("GET","POST","FILES");		
		$this->_xmlToolBox->startTag("params");
			$params = $this->_http->getParams();
			uksort($params,"strnatcasecmp");
			foreach($params as $key => $value)
			{
				$this->_xmlToolBox->startTag("param",array("method"=>"NATIVE","type" => ((is_array($value)) ? "array" : "string")));
					$this->_xmlToolBox->addFullTag("name",$key,true);
					$this->_xmlToolBox->addFullTag("value",(is_array($value)) ? str_replace("\t","    ",  SLS_String::printArray($value)) : $value,true);
				$this->_xmlToolBox->endTag("param");
			}
			$this->_xmlToolBox->startTag("param",array("method"=>"SLS","type"=>"string"));
				$this->_xmlToolBox->addFullTag("name","genericmode",true);
				$this->_xmlToolBox->addFullTag("value",$this->_generic->getGenericControllerName(),true);
			$this->_xmlToolBox->endTag("param");
			$this->_xmlToolBox->startTag("param",array("method"=>"SLS","type"=>"string"));
				$this->_xmlToolBox->addFullTag("name","genericsmode",true);
				$this->_xmlToolBox->addFullTag("value",$this->_generic->getGenericScontrollerName(),true);
			$this->_xmlToolBox->endTag("param");
			$controllerBo = array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@isBo='true']/@name"));
			$this->_xmlToolBox->startTag("param",array("method"=>"SLS","type"=>"string"));
				$this->_xmlToolBox->addFullTag("name","genericmodebo",true);
				$this->_xmlToolBox->addFullTag("value",$controllerBo,true);
			$this->_xmlToolBox->endTag("param");
			$this->_xmlToolBox->startTag("param",array("method"=>"SLS","type"=>"string"));
				$this->_xmlToolBox->addFullTag("name","request_uri",true);
				$this->_xmlToolBox->addFullTag("value",str_replace("/","|",substr($_SERVER["REQUEST_URI"],1)),true);
			$this->_xmlToolBox->endTag("param");
		$this->_xmlToolBox->endTag("params");
		
		$this->_xmlToolBox->endTag("Http");
	}
}
?>