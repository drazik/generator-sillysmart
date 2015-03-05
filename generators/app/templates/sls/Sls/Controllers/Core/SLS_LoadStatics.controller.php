<?php
/**
 * Controller SLS_LoadStatics - Include statics files
 *
 * @author Florian Collot 
 * @copyright SillySmart
 * @package SLSControllers.Core 
 * @since 1.0
 */
class SLS_LoadStaticsControllers
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
		$this->_xmlToolBox->startTag("Statics");
		$this->_xmlToolBox->addFullTag("Sls", "", false);
		if ($this->_generic->getSide() == "user" || ($this->_generic->getActionId() == $this->_generic->getActionId("SLS_Bo","ProdSettings")))
			$this->_xmlToolBox->addFullTag("Site", "", false);
		$this->_xmlToolBox->endTag("Statics");
		
		$this->_isCache = $this->_generic->isCache();
		$this->_cacheXML = new SLS_XMLToolbox(false);
		if (!$this->_isCache)
			$this->_cacheXML->startTag("controllers");
		
		// Load Core Statics
		$this->recursiveStaticLoading($this->_generic->getPathConfig("coreStaticsControllers"), 'Sls');		
		if ($this->_generic->getSide() == "user")
			$this->recursiveStaticLoading($this->_generic->getPathConfig("staticsControllers"), 'Site');
		if ($this->_generic->getActionId() == $this->_generic->getActionId("SLS_Bo","ProdSettings"))
			$this->recursiveStaticLoading($this->_generic->getPathConfig("staticsControllers"), 'Site', 'user');
		if (!$this->_isCache)
		{
			$this->_cacheXML->endTag("controllers");
			
			$writeXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSecure")."cache.xml"));			
			$writeXML->overwriteTags("//statics", $this->_cacheXML->getXML('noHeader'));
			$writeXML->saveXML($this->_generic->getPathConfig("configSecure")."cache.xml");									
		}
			
		return $this->getXML();
	}
	
	/**
	 * Read recursively path & sub-paths to include statics files
	 *
	 * @access private
	 * @param string $path the root path
	 * @param string $type the type ('core' || 'user')
	 * @param string $side user side
	 * @since 1.0
	 */
	private function recursiveStaticLoading($path, $type, $side='sls')
	{
		if ($this->_isCache)
		{
			$staticsHandle = file_get_contents($this->_generic->getPathConfig("configSecure")."cache.xml");
			$xmlCache = new SLS_XMLToolbox($staticsHandle);	
			$files = $xmlCache->getTags("//statics/controllers/".$type."/controller/file");
			$names = $xmlCache->getTags("//statics/controllers/".$type."/controller/name");
			for($i=0;$i<count($files);$i++)
			{
				$this->_generic->_time_checkpoint = microtime(true);

				include_once($this->_generic->getRoot().$files[$i]);
				$name = $names[$i];
				
				if ($side == 'sls')
				{
					$xml = "";
					
					// Static cache enabled ?
					$staticsName = strtolower(SLS_String::substrBeforeFirstDelimiter($name,"Controller"));					
					$staticsCacheVisibility = $this->_cache->getObject($staticsName,"statics","visibility");
					$staticsCacheResponsive = $this->_cache->getObject($staticsName,"statics","responsive");
					$staticsCacheExpiration = $this->_cache->getObject($staticsName,"statics","expire");					
					$staticsCache = false;
					
					if ($this->_generic->getSide() == "user" &&
						$this->_generic->getGenericControllerName() != "Default" &&
						!$this->_generic->isBo() && 
						in_array($staticsCacheVisibility,array("public","private")))
					{
						$staticsCache = true;
						
						// Partial xml cached
						if (false !== ($staticsCached = $this->_cache->getCachePartial($staticsCacheExpiration,"static",$staticsName,$staticsCacheVisibility,$staticsCacheResponsive)))
						{						
							$xml = $staticsCached;
							$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Cache (Partial): Executing Static Controller", "Controller: ".SLS_String::substrBeforeLastDelimiter($name,"Controller"),"Controller Static");
						}						
					}
					
					if (empty($xml))
					{
						$classObj = new ${name}();
						$xml = $classObj->getXML();
						$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Static Controller", "Controller: ".SLS_String::substrBeforeLastDelimiter($name,"Controller"),"Controller Static");
						
						// Save partial xml static cache
						if ($staticsCache)
							$this->_cache->saveCachePartial($xml,"static",$staticsName,$staticsCacheVisibility,$staticsCacheResponsive);
					}
						
					$this->_xmlToolBox->appendXMLNode("//Statics/".$type, $xml);
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
				$this->_generic->_time_checkpoint = microtime(true);
				
				$this->_cacheXML->startTag("controller");
				include_once($arrayResult[$i]);
				$staticName = array_shift(explode(".", SLS_String::substrAfterLastDelimiter($arrayResult[$i], "/")))."Controller";				
				if (class_exists($staticName))
				{					
					$this->_cacheXML->addFullTag("file", $arrayResult[$i], true);
					$this->_cacheXML->addFullTag("name", $staticName, true);
					
					if ($side == 'sls')
					{
						$classObj = new ${staticName}();
						$this->_xmlToolBox->appendXMLNode("//Statics/".$type, $classObj->getXML());
					}
				}
				$this->_cacheXML->endTag("controller");
				
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Static Controller", "Controller: ".SLS_String::substrBeforeLastDelimiter($staticName,"Controller"),"Controller Static");
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