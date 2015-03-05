<?php
/**
 * Controller SLS_LoadComponents - Include component files
 *
 * @author Laurent Bientz
 * @copyright SillySmart
 * @package SLSControllers.Core 
 * @since 1.0
 */
class SLS_LoadComponentsControllers
{
	private $_generic;
	private $_cache;
	private $_xmlToolBox;
	private $_tabControllers = array();
	private $_isCache;
	private $_cacheXML;
	
	/**
	 * Constructor
	 *
	 * @access public	 
	 * @since 1.0
	 */
	public function __construct() 
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_cache = $this->_generic->getObjectCache();
		$this->_xmlToolBox = new SLS_XMLToolbox();
		$this->_xmlToolBox->startTag("Components");
		$this->_xmlToolBox->addFullTag("Sls", "", false);
		if ($this->_generic->getSide() == "user" || ($this->_generic->getActionId() == $this->_generic->getActionId("SLS_Bo","ProdSettings")))
			$this->_xmlToolBox->addFullTag("Site", "", false);
		$this->_xmlToolBox->endTag("Components");		
		$this->_isCache = $this->_generic->isCache();
		$this->_cacheXML = new SLS_XMLToolbox(false);
		if (!$this->_isCache)
			$this->_cacheXML->startTag("components");
		
		// Load Core Statics
		$this->recursiveStaticLoading($this->_generic->getPathConfig("coreComponentsControllers"), 'Sls');		
		if ($this->_generic->getSide() == "user" || ($this->_generic->getActionId() == $this->_generic->getActionId("SLS_Bo","ProdSettings")))
			$this->recursiveStaticLoading($this->_generic->getPathConfig("componentsControllers"), 'Site');
		if (!$this->_isCache)
		{
			$this->_cacheXML->endTag("components");
			
			$writeXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSecure")."cache.xml"));			
			$writeXML->overwriteTags("//statics", $this->_cacheXML->getXML('noHeader'));
			$writeXML->saveXML($this->_generic->getPathConfig("configSecure")."cache.xml");			
		}
			
		return $this->getXML();
	}
	
	/**
	 * Read recursively path & sub-paths to include components files used by currect action controller
	 *
	 * @access private
	 * @param string $path the root path
	 * @param string $type the type ('core' || 'user')
	 * @since 1.0
	 */
	private function recursiveStaticLoading($path, $type)
	{
		$controllersXML = $this->_generic->getControllersXML();
		
		if ($this->_isCache)
		{			
			$staticsHandle = file_get_contents($this->_generic->getPathConfig("configSecure")."cache.xml");
			$xmlCache = new SLS_XMLToolbox($staticsHandle);	
			$files = $xmlCache->getTags("//components/".$type."/controller/file");
			$names = $xmlCache->getTags("//components/".$type."/controller/name");
			for($i=0;$i<count($files);$i++)
			{				
				$components = array_shift($controllersXML->getTagsAttributes("//controllers/controller/scontrollers/scontroller[@id='".$this->_generic->getActionId()."']",array("components")));				
				if (!empty($components))
				{
					$components = explode(",",$components["attributes"][0]["value"]);
					$components = array_map("trim",$components);
					$components = array_map("strtolower",$components);					
				}
				else
					$components = array();
				
				if (empty($components) || in_array(strtolower(trim(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($names[$i], "/"), "Controller"))),$components))
				{
					$this->_generic->_time_checkpoint = microtime(true);
					include_once($this->_generic->getRoot().$files[$i]);
					$name = $names[$i];
					$xml = "";
					
					// Component cache enabled ?
					$componentsName = strtolower(SLS_String::substrBeforeFirstDelimiter($name,"Controller"));					
					$componentsCacheVisibility = $this->_cache->getObject($componentsName,"components","visibility");
					$componentsCacheResponsive = $this->_cache->getObject($componentsName,"components","responsive");
					$componentsCacheExpiration = $this->_cache->getObject($componentsName,"components","expire");					
					$componentsCache = false;					
					
					if ($this->_generic->getSide() == "user" &&
						$this->_generic->getGenericControllerName() != "Default" &&
						!$this->_generic->isBo() && 
						in_array($componentsCacheVisibility,array("public","private")))
					{
						$componentsCache = true;
						
						// Partial xml cached
						if (false !== ($componentsCached = $this->_cache->getCachePartial($componentsCacheExpiration,"component",$componentsName,$componentsCacheVisibility,$componentsCacheResponsive)))
						{				
							$xml = $componentsCached;
							$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Cache (Partial): Executing Component Controller", "Controller: ".SLS_String::substrBeforeLastDelimiter($name,"Controller"),"Controller Component");
						}
					}
					
					if (empty($xml))
					{					
						$classObj = new $name();
						$xml = $classObj->getXML();
						$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Component Controller", "Controller: ".SLS_String::substrBeforeLastDelimiter($name,"Controller"),"Controller Component");
						
						// Save partial xml component cache
						if ($componentsCache)						
							$this->_cache->saveCachePartial($xml,"component",$componentsName,$componentsCacheVisibility,$componentsCacheResponsive);						
					}
					
					$this->_xmlToolBox->appendXMLNode("//Components/".$type, $xml);					
				}				
			}
		}
		else 
		{
			$searchedExt = array('php');
			$arrayResult = array();
			$arrayResult = $this->_generic->recursiveReadDir($path, $arrayResult, $searchedExt);
			
			$this->_cacheXML->startTag($type);
			for($i=0;$i<$count = count($arrayResult);$i++)
			{
				$components = array_shift($controllersXML->getTagsAttributes("//controllers/controller/scontrollers/scontroller[@id='".$this->_generic->getActionId()."']",array("components")));
				if (!empty($components))
				{
					$components = explode(",",$components["attributes"][0]["value"]);
					$components = array_map("trim",$components);
					$components = array_map("strtolower",$components);					
				}
				else
					$components = array();
				
				$this->_cacheXML->startTag("controller");				
				include_once($arrayResult[$i]);
				$componentName = array_shift(explode(".", SLS_String::substrAfterLastDelimiter($arrayResult[$i], "/")))."Controller";
				
				if (class_exists($componentName))
				{
					$this->_cacheXML->addFullTag("file", $arrayResult[$i], true);
					$this->_cacheXML->addFullTag("name", $componentName, true);
					if (empty($components) || in_array(strtolower(trim(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($arrayResult[$i], "/"), ".controller.php"))),$components))
					{
						$this->_generic->_time_checkpoint = microtime(true);
						$classObj = new $componentName();						
						$this->_xmlToolBox->appendXMLNode("//Components/".$type, $classObj->getXML());
						$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Component Controller", "Controller: ".SLS_String::substrBeforeLastDelimiter($componentName,"Controller"),"Controller Component");
					}
				}
				$this->_cacheXML->endTag("controller");
			}
			$this->_cacheXML->endTag($type);				
		}
	}
	
	/**
	 * Get the XML of the controller
	 *
	 * @access public
	 * @return string the XML of the controller
	 * @since 1.0
	 */
	public function getXML()
	{
		return $this->_xmlToolBox->getXML('noHeader');
	}
}	
?>