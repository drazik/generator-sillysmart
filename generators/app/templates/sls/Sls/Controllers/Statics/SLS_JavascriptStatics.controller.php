<?php
/**
 * Controller SLS_JavascriptStatics - Specific controller of statics Javascript
 *  
 * @author Florian Collot 
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @since 1.0
 */
class SLS_JavascriptStaticsController extends SLS_FrontStatic implements SLS_IStatic 
{
	private $_cacheXML;
	private $_cacheActive;
	
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
		$this->_cacheXML = new SLS_XMLToolbox();
		$this->_cacheActive = $this->_generic->isCache();

		$this->_xmlToolBox->startTag("JsStatics");		
		
		$dirStatics = $this->_generic->getPathConfig("jsStatics");
		$dirGlobals = $this->_generic->getPathConfig("js");
		$dirCoreStatics = $this->_generic->getPathConfig("coreJsStatics");
		$jsHandle = file_get_contents($this->_generic->getPathConfig("configSecure")."cache.xml");
		
		if (!$this->_cacheActive)
		{
			$this->_cacheXML->startTag("javascripts");
			$this->_arrayStatics['js']['path'] = array();
			$this->_arrayStatics['js']['filename'] = array();
			$domainName = $this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "");			
			$protocol = $this->_generic->getProtocol();
			
			$this->recursiveRead($dirStatics, $this->_arrayStatics['js']['path'], $this->_arrayStatics['js']['filename']);
			array_multisort($this->_arrayStatics['js']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['js']['path'], SORT_ASC, SORT_STRING);
			$this->_xmlToolBox->startTag("filesStatics");				
			$this->_cacheXML->startTag("filesStatics");					
			for($i=0;$i<count($this->_arrayStatics['js']['filename']);$i++)
			{
				$this->_xmlToolBox->addFullTag("file", $protocol."://".$domainName."/".$this->_arrayStatics['js']['path'][$i], true);
				$this->_cacheXML->addFullTag("file", $protocol."://".$domainName."/".$this->_arrayStatics['js']['path'][$i], true);					
			}			
			$this->_cacheXML->endTag("filesStatics");					
			$this->_xmlToolBox->endTag("filesStatics");		
			
			
			$this->_arrayStatics['js']['path'] = array();
			$this->_arrayStatics['js']['filename'] = array();
					
			$this->recursiveRead($dirCoreStatics, $this->_arrayStatics['js']['path'], $this->_arrayStatics['js']['filename']);				
			array_multisort($this->_arrayStatics['js']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['js']['path'], SORT_ASC, SORT_STRING);
			$this->_xmlToolBox->startTag("filesCoreStatics");				
			$this->_cacheXML->startTag("filesCoreStatics");					
			for($i=0;$i<count($this->_arrayStatics['js']['filename']);$i++)
			{
				
				$this->_xmlToolBox->addFullTag("file", $protocol."://".$domainName."/".$this->_arrayStatics['js']['path'][$i], true);
				$this->_cacheXML->addFullTag("file", $protocol."://".$domainName."/".$this->_arrayStatics['js']['path'][$i], true);					
			}			
			
			$this->_cacheXML->endTag("filesCoreStatics");					
			$this->_xmlToolBox->endTag("filesCoreStatics");				
			$this->_arrayStatics['js']['path'] = array();
			$this->_arrayStatics['js']['filename'] = array();
			if (substr($dirStatics, strlen($dirStatics)-1, 1) != "/")
				$dirStatics .= "/";
			$paths[0] = $dirStatics;
			$this->recursiveRead($dirGlobals, $this->_arrayStatics['js']['path'], $this->_arrayStatics['js']['filename'], $paths);				
			array_multisort($this->_arrayStatics['js']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['js']['path'], SORT_ASC, SORT_STRING);
			$this->_xmlToolBox->startTag("filesDyn");				
			$this->_cacheXML->startTag("filesDyn");					
			for($i=0;$i<count($this->_arrayStatics['js']['filename']);$i++)
			{
				$this->_xmlToolBox->addFullTag("file", $protocol."://".$domainName."/".$this->_arrayStatics['js']['path'][$i], true);
				$this->_cacheXML->addFullTag("file", $protocol."://".$domainName."/".$this->_arrayStatics['js']['path'][$i], true);					

			}	
			$this->_cacheXML->endTag("filesDyn");					
			$this->_xmlToolBox->endTag("filesDyn");		
			$this->_cacheXML->endTag("javascripts");
			
			$writeXML = new SLS_XMLToolbox($jsHandle);
			$writeXML->overwriteTags("//statics", $this->_cacheXML->getXML('noHeader'));
			$writeXML->saveXML($this->_generic->getPathConfig("configSecure")."cache.xml");			
		}
		else 
		{
			$xmlCache = new SLS_XMLToolbox($jsHandle);	
			$files = $xmlCache->getTags("//statics/javascripts/filesCoreStatics/file");
			$this->_xmlToolBox->startTag("filesCoreStatics");		
			foreach ($files as $file)
				$this->_xmlToolBox->addFullTag("file", $file, true);
				
			$this->_xmlToolBox->endTag("filesCoreStatics");	
			$files = $xmlCache->getTags("//statics/javascripts/filesStatics/file");
			$this->_xmlToolBox->startTag("filesStatics");		
			foreach ($files as $file)
				$this->_xmlToolBox->addFullTag("file", $file, true);
				
			$this->_xmlToolBox->endTag("filesStatics");
			$files = $xmlCache->getTags("//statics/javascripts/filesDyn/file");
			$this->_xmlToolBox->startTag("filesDyn");		
			foreach ($files as $file)
				$this->_xmlToolBox->addFullTag("file", $file, true);
				
			$this->_xmlToolBox->endTag("filesDyn");		
		}
		$this->_xmlToolBox->endTag("JsStatics");
		
	}
	
	
	/**
	 * Read recursively path & sub-paths of js file
	 *
	 * @access private
	 * @param string $path the root path
	 * @param array &$arrayPath reference array, it will contains paths
	 * @param array &$arrayFilename reference array, it will contains filenames
	 * @param array $pathExclu array of ignore paths
	 * @since 1.0
	 */
	private function recursiveRead($path,&$arrayPath, &$arrayFilename, $pathExclu=array())
	{ 
		if (substr($path, strlen($path)-1, 1) != "/")
			$path .= "/";
				
		if (@is_dir($path))
		{
			$handle = opendir($path);			
			while (false !== ($object = readdir($handle))) 
			{
				if (is_dir($path.$object) && substr($object, 0, 1) != ".") 
					$this->recursiveRead($path.$object,$arrayPath, $arrayFilename,$pathExclu);				
				if (substr($object, 0, 1) != "." && is_file($path.$object) && SLS_String::getFileExtension($object) == "js" && !in_array($path, $pathExclu)) 
				{
					array_push($arrayPath, $path.$object.((!SLS_String::contains($object,"?")) ? "?".$this->_generic->getSiteConfig("versionName") : ""));
					array_push($arrayFilename, $object);
				}		
			}
			closedir($handle);
		}
	}	
}
?>