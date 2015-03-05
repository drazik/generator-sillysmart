<?php
/**
 * Controlleur SLS_XslStatics - Specific controller of specifics & generics XSL
 *
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @since 1.0 
 */
class SLS_XslStaticsController  extends SLS_FrontStatic implements SLS_IStatic 
{
	private $_cacheXML;
	private $_cacheActive;
	private $_firstPass;
	private $_arrayStatics = array();
	
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
		$this->_cacheXML = new SLS_XMLToolbox(false);
		$this->_cacheActive = $this->_generic->isCache();			
		$this->_xmlToolBox->startTag("XslStatics");
		
		// Recover path of Statics Unloaded
		#$dirStaticsUnloaded = $this->_generic->getPathConfig("viewsGenericsUnloaded");
		$dirStaticsUnloaded = $this->_generic->getPathConfig("viewsGenerics");
		// Recover path of Statics Loaded Body
		#$dirStaticsLoadedBody = $this->_generic->getPathConfig("viewsGenericsLoadedBody");
		// Recover path of Statics Loaded Headers
		#$dirStaticsLoadedHeaders = $this->_generic->getPathConfig("viewsGenericsLoadedHeaders");
		
		// Recover path of SLS Statics Unloaded
		$dirSlsStaticsUnloaded = $this->_generic->getPathConfig("slsViewsGenericsUnloaded");
		// Recover path of SLS Statics Loaded Body
		$dirSlsStaticsLoadedBody = $this->_generic->getPathConfig("slsViewsGenericsLoadedBody");
		// Recover path of SLS Statics Loaded Headers
		$dirSlsStaticsLoadedHeaders = $this->_generic->getPathConfig("slsViewsGenericsLoadedHeaders");
		
		// Recover path of Core Statics Unloaded
		$dirCoreStaticsUnloaded = $this->_generic->getPathConfig("coreViewsGenericsUnloaded");
		// Recover path of Core Statics Loaded Body
		$dirCoreStaticsLoadedBody = $this->_generic->getPathConfig("coreViewsGenericsLoadedBody");
		// Recover path of Core Statics Loaded Headers
		$dirCoreStaticsLoadedHeaders = $this->_generic->getPathConfig("coreViewsGenericsLoadedHeaders");
				
		$xslHandle = file_get_contents($this->_generic->getPathConfig("configSecure")."cache.xml");
		
		// If cache not active
		if (!$this->_cacheActive)
		{						
			$this->_xmlToolBox->startTag("files");
			$this->_cacheXML->startTag("xsls");
			$this->_cacheXML->startTag("files");			
			// Put in cache.xml User Unloaded Files but in current XML only if side == 'user'
			($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("Unloaded") : "";
			$this->_cacheXML->startTag("Unloaded");				
				$this->_arrayStatics['unloaded']['templateName'] = array();
				$this->_arrayStatics['unloaded']['path'] = array();
				$this->_arrayStatics['unloaded']['filename'] = array();				
				$this->recursiveRead($dirStaticsUnloaded, false, $this->_arrayStatics['unloaded']['templateName'], $this->_arrayStatics['unloaded']['path'], $this->_arrayStatics['unloaded']['filename']);								
				array_multisort($this->_arrayStatics['unloaded']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['unloaded']['path'], SORT_ASC, SORT_STRING);				
				for($i=0;$i<count($this->_arrayStatics['unloaded']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("xsl") : "";						
						($this->_generic->getSide() == "user") ? $this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['unloaded']['path'][$i], true) : "";
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['unloaded']['path'][$i], true);					
					($this->_generic->getSide() == "user") ? $this->_xmlToolBox->endTag("xsl") : "";
					$this->_cacheXML->endTag("xsl");
				}				
				unset($this->_arrayStatics['unloaded']);
			$this->_cacheXML->endTag("Unloaded");
			($this->_generic->getSide() == "user") ? $this->_xmlToolBox->endTag("Unloaded") : "";	
			
			// Put in cache.xml the SLS Unloaded Files but in current XML only if side == 'sls'
			($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("Unloaded") : "";
			$this->_cacheXML->startTag("SlsUnloaded");				
				$this->_arrayStatics['unloaded']['templateName'] = array();
				$this->_arrayStatics['unloaded']['path'] = array();
				$this->_arrayStatics['unloaded']['filename'] = array();				
				$this->recursiveRead($dirSlsStaticsUnloaded, false, $this->_arrayStatics['unloaded']['templateName'], $this->_arrayStatics['unloaded']['path'], $this->_arrayStatics['unloaded']['filename']);				
				array_multisort($this->_arrayStatics['unloaded']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['unloaded']['path'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['unloaded']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("xsl") : "";						
						($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['unloaded']['path'][$i], true) : "";
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['unloaded']['path'][$i], true);					
					($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->endTag("xsl") : "";
					$this->_cacheXML->endTag("xsl");
				}				
				unset($this->_arrayStatics['unloaded']);
			$this->_cacheXML->endTag("SlsUnloaded");
			($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->endTag("Unloaded") : "";
			
			// Put in cache.xml the Core Unloaded Files &&t in current XML
			$this->_xmlToolBox->startTag("CoreUnloaded");
			$this->_cacheXML->startTag("CoreUnloaded");				
				$this->_arrayStatics['unloaded']['templateName'] = array();
				$this->_arrayStatics['unloaded']['path'] = array();
				$this->_arrayStatics['unloaded']['filename'] = array();				
				$this->recursiveRead($dirCoreStaticsUnloaded, false, $this->_arrayStatics['unloaded']['templateName'], $this->_arrayStatics['unloaded']['path'], $this->_arrayStatics['unloaded']['filename']);				
				array_multisort($this->_arrayStatics['unloaded']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['unloaded']['path'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['unloaded']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					$this->_xmlToolBox->startTag("xsl");						
						$this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['unloaded']['path'][$i], true);
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['unloaded']['path'][$i], true);					
					$this->_xmlToolBox->endTag("xsl");
					$this->_cacheXML->endTag("xsl");
				}				
				unset($this->_arrayStatics['unloaded']);
			$this->_cacheXML->endTag("CoreUnloaded");
			$this->_xmlToolBox->endTag("CoreUnloaded");	
			
			// Put in cache.xml the User loaded Body Files but in current XML only if side == user
			($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("Loaded") : "";
			$this->_cacheXML->startTag("Loaded");
				/*($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("Body") : "";
				$this->_cacheXML->startTag("Body");
					$this->_arrayStatics['loadedBody']['templateName'] = array();
					$this->_arrayStatics['loadedBody']['path'] = array();
					$this->_arrayStatics['loadedBody']['filename'] = array();
				$this->recursiveRead($dirStaticsLoadedBody, true, $this->_arrayStatics['loadedBody']['templateName'], $this->_arrayStatics['loadedBody']['path'], $this->_arrayStatics['loadedBody']['filename']);					
				array_multisort($this->_arrayStatics['loadedBody']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedBody']['path'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedBody']['templateName'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['loadedBody']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("xsl") : "";
						($this->_generic->getSide() == "user") ? $this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['loadedBody']['path'][$i], true) : "";
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['loadedBody']['path'][$i], true);
						($this->_generic->getSide() == "user") ? $this->_xmlToolBox->addFullTag("templateName", $this->_arrayStatics['loadedBody']['templateName'][$i], true) : "";
						$this->_cacheXML->addFullTag("templateName", $this->_arrayStatics['loadedBody']['templateName'][$i], true);						
					($this->_generic->getSide() == "user") ? $this->_xmlToolBox->endTag("xsl") : "";
					$this->_cacheXML->endTag("xsl");
				}
				unset($this->_arrayStatics['loadedBody']);				
				$this->_cacheXML->endTag("Body");
				($this->_generic->getSide() == "user") ?$this->_xmlToolBox->endTag("Body") : "";*/

				// Put in cache.xml the Sls loaded Body Files but in current XML only if side == sls
				($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("Loaded") : "";
				/*($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("Body") : "";
				$this->_cacheXML->startTag("SlsBody");
					$this->_arrayStatics['loadedBody']['templateName'] = array();
					$this->_arrayStatics['loadedBody']['path'] = array();
					$this->_arrayStatics['loadedBody']['filename'] = array();
				$this->recursiveRead($dirStaticsLoadedBody, true, $this->_arrayStatics['loadedBody']['templateName'], $this->_arrayStatics['loadedBody']['path'], $this->_arrayStatics['loadedBody']['filename']);					
				array_multisort($this->_arrayStatics['loadedBody']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedBody']['path'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedBody']['templateName'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['loadedBody']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("xsl") : "";
						($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['loadedBody']['path'][$i], true) : "";
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['loadedBody']['path'][$i], true);
						($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->addFullTag("templateName", $this->_arrayStatics['loadedBody']['templateName'][$i], true) : "";
						$this->_cacheXML->addFullTag("templateName", $this->_arrayStatics['loadedBody']['templateName'][$i], true);						
					($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->endTag("xsl") : "";
					$this->_cacheXML->endTag("xsl");
				}
				unset($this->_arrayStatics['loadedBody']);				
				$this->_cacheXML->endTag("SlsBody");
				($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->endTag("Body") : "";*/	
				
				// Put in cache.xml the Core loaded Body Files && in current XML
				$this->_xmlToolBox->startTag("CoreBody");
				$this->_cacheXML->startTag("CoreBody");
					$this->_arrayStatics['loadedBody']['templateName'] = array();
					$this->_arrayStatics['loadedBody']['path'] = array();
					$this->_arrayStatics['loadedBody']['filename'] = array();
				$this->recursiveRead($dirCoreStaticsLoadedBody, true, $this->_arrayStatics['loadedBody']['templateName'], $this->_arrayStatics['loadedBody']['path'], $this->_arrayStatics['loadedBody']['filename']);					
				array_multisort($this->_arrayStatics['loadedBody']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedBody']['path'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedBody']['templateName'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['loadedBody']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					$this->_xmlToolBox->startTag("xsl");						
						$this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['loadedBody']['path'][$i], true);
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['loadedBody']['path'][$i], true);
						$this->_xmlToolBox->addFullTag("templateName", $this->_arrayStatics['loadedBody']['templateName'][$i], true);
						$this->_cacheXML->addFullTag("templateName", $this->_arrayStatics['loadedBody']['templateName'][$i], true);						
					$this->_xmlToolBox->endTag("xsl");
					$this->_cacheXML->endTag("xsl");
				}
				unset($this->_arrayStatics['loadedBody']);					
				$this->_cacheXML->endTag("CoreBody");
				$this->_xmlToolBox->endTag("CoreBody");	
				
				// Put in cache.xml the User loaded Headers Files but in current XML only if side == user
				/*($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("Headers") : "";
				$this->_cacheXML->startTag("Headers");
					$this->_arrayStatics['loadedHeaders']['templateName'] = array();
					$this->_arrayStatics['loadedHeaders']['path'] = array();
					$this->_arrayStatics['loadedHeaders']['filename'] = array();
					$this->recursiveRead($dirStaticsLoadedHeaders, true, $this->_arrayStatics['loadedHeaders']['templateName'], $this->_arrayStatics['loadedHeaders']['path'], $this->_arrayStatics['loadedHeaders']['filename']);					
					array_multisort($this->_arrayStatics['loadedHeaders']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedHeaders']['path'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedHeaders']['templateName'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['loadedHeaders']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					($this->_generic->getSide() == "user") ? $this->_xmlToolBox->startTag("xsl") : "";						
						($this->_generic->getSide() == "user") ? $this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['loadedHeaders']['path'][$i], true) : "";
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['loadedHeaders']['path'][$i], true);
						($this->_generic->getSide() == "user") ? $this->_xmlToolBox->addFullTag("templateName", $this->_arrayStatics['loadedHeaders']['templateName'][$i], true) : "";
						$this->_cacheXML->addFullTag("templateName", $this->_arrayStatics['loadedHeaders']['templateName'][$i], true);						
					($this->_generic->getSide() == "user") ? $this->_xmlToolBox->endTag("xsl") : "";
					$this->_cacheXML->endTag("xsl");
				}
				unset($this->_arrayStatics['loadedHeaders']);
				$this->_cacheXML->endTag("Headers");				
				($this->_generic->getSide() == "user") ? $this->_xmlToolBox->endTag("Headers") : "";
				
				// Put in cache.xml the Sls loaded Headers Files but in current XML only if side == sls
				/*($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("Headers") : "";
				$this->_cacheXML->startTag("SlsHeaders");
					$this->_arrayStatics['loadedHeaders']['templateName'] = array();
					$this->_arrayStatics['loadedHeaders']['path'] = array();
					$this->_arrayStatics['loadedHeaders']['filename'] = array();
					$this->recursiveRead($dirStaticsLoadedHeaders, true, $this->_arrayStatics['loadedHeaders']['templateName'], $this->_arrayStatics['loadedHeaders']['path'], $this->_arrayStatics['loadedHeaders']['filename']);					
					array_multisort($this->_arrayStatics['loadedHeaders']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedHeaders']['path'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedHeaders']['templateName'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['loadedHeaders']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->startTag("xsl") : "";						
						($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['loadedHeaders']['path'][$i], true) : "";
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['loadedHeaders']['path'][$i], true);
						($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->addFullTag("templateName", $this->_arrayStatics['loadedHeaders']['templateName'][$i], true) : "";
						$this->_cacheXML->addFullTag("templateName", $this->_arrayStatics['loadedHeaders']['templateName'][$i], true);						
					($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->endTag("xsl") : "";
					$this->_cacheXML->endTag("xsl");
				}
				unset($this->_arrayStatics['loadedHeaders']);
				$this->_cacheXML->endTag("SlsHeaders");
				($this->_generic->getSide() == "sls") ? $this->_xmlToolBox->endTag("Headers") : "";*/
				
				// Put in cache.xml the Core loaded Headers Files && in current XML
				$this->_xmlToolBox->startTag("CoreHeaders");
				$this->_cacheXML->startTag("CoreHeaders");
					$this->_arrayStatics['loadedHeaders']['templateName'] = array();
					$this->_arrayStatics['loadedHeaders']['path'] = array();
					$this->_arrayStatics['loadedHeaders']['filename'] = array();
					$this->recursiveRead($dirCoreStaticsLoadedHeaders, true, $this->_arrayStatics['loadedHeaders']['templateName'], $this->_arrayStatics['loadedHeaders']['path'], $this->_arrayStatics['loadedHeaders']['filename']);					
					array_multisort($this->_arrayStatics['loadedHeaders']['filename'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedHeaders']['path'], SORT_ASC, SORT_STRING, $this->_arrayStatics['loadedHeaders']['templateName'], SORT_ASC, SORT_STRING);
				for($i=0;$i<count($this->_arrayStatics['loadedHeaders']['filename']);$i++)
				{
					$this->_cacheXML->startTag("xsl");
					$this->_xmlToolBox->startTag("xsl");						
						$this->_xmlToolBox->addFullTag("file", $this->_arrayStatics['loadedHeaders']['path'][$i], true);
						$this->_cacheXML->addFullTag("file", $this->_arrayStatics['loadedHeaders']['path'][$i], true);
						$this->_xmlToolBox->addFullTag("templateName", $this->_arrayStatics['loadedHeaders']['templateName'][$i], true);
						$this->_cacheXML->addFullTag("templateName", $this->_arrayStatics['loadedHeaders']['templateName'][$i], true);						
					$this->_xmlToolBox->endTag("xsl");
					$this->_cacheXML->endTag("xsl");
				}
				unset($this->_arrayStatics['loadedHeaders']);	
				$this->_cacheXML->endTag("CoreHeaders");
				$this->_xmlToolBox->endTag("CoreHeaders");
				
			$this->_cacheXML->endTag("Loaded");
			$this->_xmlToolBox->endTag("Loaded");
			$this->_cacheXML->endTag("files");
			$this->_xmlToolBox->endTag("files");
			$this->_cacheXML->endTag("xsls");
			$writeXML = new SLS_XMLToolbox($xslHandle);
			$writeXML->overwriteTags("//statics", $this->_cacheXML->getXML());
			$writeXML->saveXML($this->_generic->getPathConfig("configSecure")."cache.xml");			
		}
		else 		
			$this->_xmlToolBox->addValue(SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($xslHandle, "</xsls>"), "<xsls>"));
		
		$this->_xmlToolBox->endTag("XslStatics");
	}
	
	/**
	 * Read recursively path & sub-paths of xsl file
	 *
	 * @access private
	 * @param string $path the root path
	 * @param bool $searchTemplateName true if you want to recover templates names
	 * @param array &$arrayTemplateName reference array, it will contains templates names
	 * @param array &$arrayPath reference array, it will contains paths
	 * @param array &$arrayFilename reference array, it will contains filenames
	 * @since 1.0
	 */
	private function recursiveRead($path, $searchTemplateName=false, &$arrayTemplateName, &$arrayPath, &$arrayFilename)
	{ 
		if (substr($path, strlen($path)-1, 1) != "/")
			$path .= "/";
		if (@is_dir($path))
		{
			$handle = opendir($path);			
			while (false !== ($object = readdir($handle))) 
			{
				if (is_dir($path.$object) && substr($object, 0, 1) != ".") 
					$this->recursiveRead($path.$object, $searchTemplateName, $arrayTemplateName, $arrayPath, $arrayFilename);				
				if (substr($object, 0, 1) != "." && is_file($path.$object) && SLS_String::getFileExtension($object) == "xsl") 
				{
					if ($searchTemplateName)
					{
						$xslHandle = file_get_contents($path.$object);
						$boundContent = SLS_String::trimString(array_shift(SLS_String::getBoundContent($xslHandle, "<xsl:template", ">")));
						if (($stringName = stristr($boundContent, "name=")) === false)
						{
							array_push($arrayTemplateName, "");
						}
						else
						{
							$templateName = explode($stringName{5}, $stringName);
							array_push($arrayTemplateName, $templateName[1]);
						}
					}
					array_push($arrayPath, $path.$object);
					array_push($arrayFilename, $object);
				}		
			}
			closedir($handle);
		}
	}
}
?>