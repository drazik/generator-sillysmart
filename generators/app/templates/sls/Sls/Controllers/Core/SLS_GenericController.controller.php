<?php
/**
 * Controller Generic - Father's controller of each Actions Controllers
 *
 * @author Florian Collot
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Core 
 * @since 1.0
 */
class SLS_GenericController
{
	protected $_generic;	
	protected $_xml;
	protected $_xmlToolBox;
	protected $_http;
	protected $_session;
	protected $_lang;
	protected $_cache;
	protected $_security;
	protected $_loadStaticsJs = true;
	protected $_loadDynsJs = false;
	protected $_jsMultiLang = false;
	protected $_buildConfigsJsVars = false;
	protected $_output = "xhtml";
	protected $_outputOptions = null;
	protected $_pageTitle = "";
	protected $_cacheSaved = false;
	protected $_metas = array("description"=>"","keywords"=>"","author"=>"","copyright"=>"","robots"=>"","favicon"=>"");
	
	/**
	 * Constructor
	 *
	 * @access public	 
	 * @since 1.0
	 */
	public function __construct() 
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_http = $this->_generic->getObjectHttpRequest();
		$this->_session = $this->_generic->getObjectSession();		
		$this->_lang = $this->_generic->getObjectLang();
		$this->_cache = $this->_generic->getObjectCache();
		$this->_security = $this->_generic->getObjectSecurity();
		$this->_xml = $this->_generic->getBufferXML();		
		$initControllerXml = new SLS_XMLToolbox($this->_xml);
		$initControllerXml->appendXMLNode("//root", "<View></View>");
		$this->_xml = $initControllerXml->getXML();
		$this->_generic->setBufferXml($this->_xml, false);
		$this->_loadStaticsJs = ($this->_generic->getSiteConfig("defaultLoadStaticsJavascript") == 1) ? true : false;
		$this->_loadDynsJs = ($this->_generic->getSiteConfig("defaultLoadDynsJavascript") == 1) ? true : false;
		$this->_jsMultiLang = ($this->_generic->getSiteConfig("defaultMultilanguageJavascript") == 1) ? true : false;
		$this->_buildConfigsJsVars = ($this->_generic->getSiteConfig("defaultBuildConfigsJsVars") == 1) ? true : false;
		$this->recoverInitialMetas();
	}
	
	/**
	 * Get the XML of the current controller
	 *
	 * @access public
	 * @return string the final xml of the current controller
	 * @since 1.0
	 */
	public function getFinalXML()
	{
		$bufferXml = new SLS_XMLToolbox($this->_generic->getBufferXML());
		
		// Sls cached enabled and Action cache enabled ?
		$cacheOptions = $this->_cache->getAction();
		if ($this->_generic->isCache() && 
			$this->_generic->getSide() == "user" &&
			$this->_generic->getGenericControllerName() != "Default" &&
			is_array($cacheOptions) && 
			count($cacheOptions) == 4)
		{
			$actionCacheVisibility 	= $cacheOptions[0];
			$actionCacheScope 		= $cacheOptions[1];
			$actionCacheResponsive  = $cacheOptions[2];
			$actionCacheExpiration 	= $cacheOptions[3];
			
			// Get partial xml action cache
			if ($actionCacheScope == "partial" && !$this->_cacheSaved && false !== ($actionCached = $this->_cache->getCachePartial($actionCacheExpiration,"action","action_".SLS_String::substrAfterFirstDelimiter($this->_generic->getActionId(),"a_"),$actionCacheVisibility,$actionCacheResponsive)))	
			{
				$this->_generic->_time_checkpoint = microtime(true);				
				$bufferXml->appendXMLNode("//root/View", $actionCached);
				$this->_xml = $bufferXml->getXML();
				$this->_generic->setBufferXml($this->_xml, false);
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Cache (Partial): Executing Action","","Controller Action");
			} 
		}
		
		$this->_generic->setBufferXML($this->_xml, false);		
		if (empty($this->_pageTitle))
			$this->setMetaTitle();
		$this->writeParams();
		$this->_xml = $this->_generic->getBufferXml();		
		return $this->_xml;
	}
	
	/**
	 * You can call this function to have a new Instance of SLS_XMLToolbox in your controller
	 * 
	 * @access protected
	 * @return SLS_XMLToolbox $xml SLS_XMLToolbox instance
	 * @since 1.0
	 */
	protected function getXML()
	{
		return new SLS_XMLToolbox(false);		
	}
	
	/**
	 * Function saved SLS_XMLToolbox object of your running controller
	 * 
	 * @access protected
	 * @param SLS_XMLToolbox $xmlObject a SLS_XMLToolbox object
	 * @since 1.0
	 */
	protected function saveXML($xmlObject) 
	{
		$bufferXml = new SLS_XMLToolbox($this->_generic->getBufferXML());
		
		$xmlAction = $xmlObject->getXML();
		
		// Sls cached enabled and Action cache enabled ?
		$cacheOptions = $this->_cache->getAction();
		if ($this->_generic->isCache() && 
			$this->_generic->getSide() == "user" && 
			$this->_generic->getGenericControllerName() != "Default" &&
			is_array($cacheOptions) && 
			count($cacheOptions) == 4)
		{
			$actionCacheVisibility 	= $cacheOptions[0];
			$actionCacheScope 		= $cacheOptions[1];
			$actionCacheResponsive  = $cacheOptions[2];
			$actionCacheExpiration 	= $cacheOptions[3];
			
			// Save partial xml action cache
			if ($actionCacheScope == "partial")
			{
				$this->_cache->saveCachePartial($xmlAction,"action","action_".SLS_String::substrAfterFirstDelimiter($this->_generic->getActionId(),"a_"),$actionCacheVisibility,$actionCacheResponsive);
				$this->_cacheSaved = true;
			}
		}
		
		$bufferXml->appendXMLNode("//root/View", $xmlAction);
		$this->_xml = $bufferXml->getXML();
		$this->_generic->setBufferXml($this->_xml, false);
	}
		
	/**
	 * XML setter
	 * 
	 * @access public
	 * @param string $xml the xml to set
	 * @since 1.0
	 */
	public function setXML($xml)
	{
		$this->_generic->setBufferXML($xml, false);	
		$this->_xml = $xml;
	}
	
	/**
	 * Setter for loading of generics javascripts
	 *
	 * @access protected
	 * @param bool $bool true if you want to load them, else false
	 * @see SLS_GenericController:loadDynsJavascripts
	 * @see SLS_GenericController:buildConfigsJsVars
	 * @since 1.0
	 */
	protected function loadStaticsJavascripts($bool)
	{
		if (is_bool($bool))
			$this->_loadStaticsJs = $bool;		
	}
	
	/**
	 * Setter for loading of Dynamics javascripts
	 *
	 * @access protected
	 * @param bool $bool true if you want to load them, else false
	 * @see SLS_GenericController:loadStaticsJavascripts
	 * @see SLS_GenericController:buildConfigsJsVars
	 * @since 1.0
	 */
	protected function loadDynsJavascripts($bool)
	{
		if (is_bool($bool))
			$this->_loadDynsJs = $bool;		
	}
	
	/**
	 * Build javascript vars
	 *
	 * @access protected
	 * @param bool $bool true if you want to build
	 * @see SLS_GenericController:loadStaticsJavascripts
	 * @see SLS_GenericController:loadDynsJavascripts
	 * @since 1.0
	 */
	protected function buildConfigsJsVars($bool)
	{
		if (is_bool($bool)) 
			$this->_buildConfigsJsVars = $bool;
	}
	
	/**
	 * Setter for Multilangage support in Javacript
	 *
	 * @access protected
	 * @param bool $bool true if you want Multilangage support
	 * @since 1.0
	 */
	protected function jsMultilang($bool)
	{
		if (is_bool($bool))
			$this->_jsMultiLang = $bool;		
	}
	
	/**	 
	 * Write Controller' specifics params into XML before send it
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function writeParams()
	{
		// We check if we aren't passed here with the dispatcher
		if ($this->_output == "xhtml")
		{
			if (strpos($this->_generic->getBufferXML(),"<ControllerParams>") === false)
			{
				$controllersParams = new SLS_XMLToolbox(false);
				$controllersParams->startTag("action");				
				$controllersParams->startTag("metas");
					$controllersParams->addFullTag("title", $this->_pageTitle, true);
					$controllersParams->addFullTag("description", $this->_metas['description'], true);
					$controllersParams->addFullTag("keywords", $this->_metas['keywords'], true);
					$controllersParams->addFullTag("robots", $this->_metas['robots'], true);
					$controllersParams->addFullTag("author", $this->_metas['author'], true);
					$controllersParams->addFullTag("copyright", $this->_metas['copyright'], true);
					$controllersParams->addFullTag("favicon", $this->_metas['favicon'], true);
				$controllersParams->endTag("metas");
				$controllersParams->startTag("links");
				$links = $this->_generic->getRegisteredLinks();
				for($i=0;$i<count($links);$i++)
				{
					$controllersParams->startTag("link");
					$controllersParams->addFullTag("name", strtoupper($links[$i]['codeName']), true);
					$controllersParams->addFullTag("href", $links[$i]['href'], true);
					$controllersParams->endTag("link");
				}
				$controllersParams->endTag("links");
				$controllersParams->endTag("action");
				
				// Save it into the XML's buffer
				$this->_generic->setBufferXML($controllersParams->getXML(), true, "//root/Statics/Sls/Configs");
			}
			// Set Protocol
			$xml = new SLS_XMLToolbox($this->_generic->getBufferXML());			
			$xml->setTag("//root/Statics/Sls/Configs/site/protocol", $this->_generic->getProtocol(), true);
			$xml->setTag("//root/Statics/Sls/Configs/site/siteprotocol", $this->_generic->getSiteConfig('protocol'), true);
			
			$this->_generic->setBufferXML($xml->getXML(), false);
		}
		elseif ($this->_output == "json")
		{			
			$xml = new SLS_XMLToolbox($this->_generic->getBufferXML());
			$output = json_encode(SLS_XMLToArray::createArray($xml->getNode("//root/View")));
			header('Content-Type: application/json');
			header('X-Robots-Tag: noindex,nofollow,noarchive');
			print($output);
			exit;			
		}
		else if ($this->_output == "rss" || $this->_output == "atom")
		{
			if ($this->_output == "rss")
				header('Content-Type: application/rss+xml');
			else 
				header('Content-Type: application/atom+xml');
			print($this->_outputOptions); 
			exit;
		}
		else 
		{
			header("Content-type: text/xml");
			if (!is_null($this->_outputOptions))
				print($this->_outputOptions);
			else 
			{
				$xml = new SLS_XMLToolbox($this->_generic->getBufferXML());
				$view = $xml->getNode("//root/View");
				print('<?xml version="1.0" encoding="UTF-8"?>'.$view);
			}
			exit;				
		}
	}
		
	/**
	 * Set The output of your action
	 *
	 * @access protected
	 * @param string $type
	 * @param string $xml the XML String witch be used in case of RSS, ATOM and XML
	 * @since 1.0
	 */
	protected function setOutput($type, $xml=null)
	{
		$type = strtolower($type);
		if ($type != "xhtml" && $type != "xml" && $type != "json" && $type != "rss" && $type != "atom")
			SLS_Tracing::addTrace(new Exception("The type requested is not supported: ".$type));
		if (($type == "rss" || $type == "atom") && is_null($xml))
			SLS_Tracing::addTrace(new Exception("You need to give an XML String to configure your Output ".$type));
		if (!is_null($xml))
			$this->_outputOptions = $xml;
		$this->_output = $type;
	}
	
	/**
	 * Alias of the same function in the Generic Class
	 *
	 * @access protected
	 * @param string $codeName
	 * @param string $controller the generic controller name
	 * @param string $scontroller the generic action name
	 * @param array $args arguments 
	 * <code>
	 * array('key1' => 'value1', 'key2' => 'value2', .., 'keyN' => '..')
	 * </code>
	 * You can set null to set no arguments or false to have an incomplete url like http://www.example.com/Controller/Action
	 * @param bool $projectDomain
	 * @param bool $https true if you want https protocol, else http
	 * @since 1.0
	 */
	protected function registerLink($codeName, $controller, $scontroller=null, $args=null, $projectDomain=true, $https=false)
	{
		$this->_generic->registerLink($codeName, $controller, $scontroller, $args, $projectDomain, $https);
	}
	
	/**
	 * Function called in tonstructor to get initial metas of the action
	 *
	 * @access private
	 * @since 1.0
	 */
	private function recoverInitialMetas()
	{
		$actionId = $this->_generic->getActionId();
		$actionDesc = array_shift($this->_generic->getCoreXML('metas')->getTags("//sls_configs/action[@id='".$actionId."']/description[@lang='".$this->_generic->getObjectLang()->getLang()."']"));
		$actionKeywords = array_shift($this->_generic->getCoreXML('metas')->getTags("//sls_configs/action[@id='".$actionId."']/keywords[@lang='".$this->_generic->getObjectLang()->getLang()."']"));
		$actionRobots = array_shift($this->_generic->getCoreXML('metas')->getTags("//sls_configs/action[@id='".$actionId."']/robots"));
		$this->_metas['author'] = $this->_generic->getSiteConfig("metaAuthor");
		$this->_metas['copyright'] = $this->_generic->getSiteConfig("metaCopyright");
		$this->_metas['description'] = (!empty($actionDesc)) ? $actionDesc : $this->_generic->getSiteConfig("metaDescription");
		$this->_metas['keywords'] = (!empty($actionKeywords)) ? $actionKeywords.", ".$this->_generic->getSiteConfig("metaKeywords") : $this->_generic->getSiteConfig("metaKeywords");
		$this->_metas['robots'] = $actionRobots;
		if ($this->_generic->getSide() == 'user' && is_file("favicon.ico"))
			$this->_metas['favicon'] = $this->_generic->getProtocol()."://".$this->_generic->getSiteConfig('domainName')."/favicon.ico";		
		else 			
			$this->_metas['favicon'] = $this->_generic->getProtocol()."://".$this->_generic->getSiteConfig('domainName')."/".$this->_generic->getPathConfig("coreIcons")."favicon.ico";
	}
	
	/**
	 * Set the title of the current page
	 *
	 * @access protected
	 * @param string $title the title of the current page
	 * @param bool $incremental true to add a new one, false to overwrite
	 * @see SLS_GenericController::setMetaRobots
	 * @see SLS_GenericController::setMetaAuthor
	 * @see SLS_GenericController::setMetaCopyright
	 * @see SLS_GenericController::setMetaKeywords
	 * @see SLS_GenericController::setMetaDescription
	 * @since 1.0
	 */
	protected function setMetaTitle($title="", $incremental=false)
	{
		if (empty($title))
		{
			// Try to recover title from metas.xml			
			$currentTitle = array_shift($this->_generic->getCoreXML('metas')->getTags("//sls_configs/action[@id='".$this->_generic->getActionId()."']/title[@lang='".$this->_generic->getObjectLang()->getLang()."']"));
			if (!empty($currentTitle))
				$this->_pageTitle = $currentTitle." | ".$this->_generic->getSiteConfig("projectName");
			
			// Else, recover title from Controller & Action names
			else 
			{
				if ($this->_generic->getSiteConfig('isInstall') == 1)
				{
					$currentTitle = array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getGenericControllerName()."']/scontrollers/scontroller[@name='".$this->_generic->getGenericScontrollerName()."']/scontrollerLangs/scontrollerLang[@lang='".$this->_generic->getObjectLang()->getLang()."']/@title"));
					if (!empty($currentTitle))
						$this->_pageTitle = $currentTitle." | ".$this->_generic->getSiteConfig("projectName");
				}
				else 
				{
					$controllers = $this->_generic->getTranslatedController($this->_generic->getGenericControllerName(), $this->_generic->getGenericScontrollerName());
					$this->_pageTitle = $controllers['controller']." - ".$controllers['scontroller']." | ".$this->_generic->getSiteConfig("projectName");
				}
			}
		}
		else
		{
			if ($incremental === false)
			{
				$this->_pageTitle = $title;
				if (!SLS_String::endsWith($this->_pageTitle," | ".$this->_generic->getSiteConfig("projectName")))
					$this->_pageTitle .= " | ".$this->_generic->getSiteConfig("projectName");
			}
			else
			{
				if (SLS_String::endsWith($this->_pageTitle," | ".$this->_generic->getSiteConfig("projectName")))
					$this->_pageTitle = SLS_String::substrBeforeLastDelimiter($this->_pageTitle," | ".$this->_generic->getSiteConfig("projectName"))." ".$title." | ".$this->_generic->getSiteConfig("projectName");
				else
				{
					$this->_pageTitle .= " ".$title;
					if (!SLS_String::endsWith($this->_pageTitle," | ".$this->_generic->getSiteConfig("projectName")))
						$this->_pageTitle .= " | ".$this->_generic->getSiteConfig("projectName");
				}
			}
		}
	}
	
	/**
	 * Set or Add an Author
	 *
	 * @access protected
	 * @param string $name the author name
	 * @param bool $incremental true to add a new one, false to overwrite
	 * @see SLS_GenericController::setMetaTitle
	 * @see SLS_GenericController::setMetaCopyright
	 * @see SLS_GenericController::setMetaKeywords
	 * @see SLS_GenericController::setMetaDescription
	 * @see SLS_GenericController::setMetaRobots
	 * @since 1.0
	 */
	protected function setMetaAuthor($name, $incremental=false)
	{
		if ($incremental === false)
			$this->_metas["author"] = $name;
		else 
			$this->_metas["author"] .= ",".$name;
	}
	
	/**
	 * Set the Copyright of the page
	 *
	 * @access protected
	 * @param string $name the copyright
	 * @param bool $incremental true to add a new one, false to overwrite
	 * @see SLS_GenericController::setMetaTitle
	 * @see SLS_GenericController::setMetaAuthor
	 * @see SLS_GenericController::setMetaKeywords
	 * @see SLS_GenericController::setMetaDescription
	 * @see SLS_GenericController::setMetaRobots
	 * @since 1.0
	 */
	protected function setMetaCopyright($name, $incremental=false)
	{
		$this->_metas["copyright"] = $name;
	}
	
	/**
	 * Set keywords or add new keywords
	 *
	 * @access protected
	 * @param string $keywords
	 * @param bool $incremental true to add a new one, false to overwrite
	 * @see SLS_GenericController::setMetaTitle
	 * @see SLS_GenericController::setMetaAuthor
	 * @see SLS_GenericController::setMetaCopyright
	 * @see SLS_GenericController::setMetaDescription
	 * @see SLS_GenericController::setMetaRobots
	 * @since 1.0
	 */
	protected function setMetaKeywords($keywords, $incremental=false)
	{
		if ($incremental === false)
			$this->_metas["keywords"] = $keywords;
		else
			$this->_metas['keywords'] = $keywords.", ".$this->_metas['keywords'];
	}
	
	/**
	 * Set a Description or complete the current
	 *
	 * @access protected
	 * @param string $description the description
	 * @param bool $incremental true to add a new one, false to overwrite
	 * @see SLS_GenericController::setMetaTitle
	 * @see SLS_GenericController::setMetaAuthor
	 * @see SLS_GenericController::setMetaCopyright
	 * @see SLS_GenericController::setMetaKeywords
	 * @see SLS_GenericController::setMetaRobots
	 * @since 1.0
	 */
	protected function setMetaDescription($description, $incremental=false)
	{
		if ($incremental === false)
			$this->_metas["description"] = $description;
		else		
			$this->_metas['description'] .= " ".$description;		
	}
	
	/**
	 * Set Robot Behaviour
	 *
	 * @access protected
	 * @param string $robot robots metas
	 * @param bool $incremental true to add a new one, false to overwrite
	 * <code>
	 * array(
	 * 		"index, follow",
	 * 		"onindex, nofollow",
	 * 		"index, nofollow",
	 * 		"noindex, follow"
	 * )
	 * </code>
	 * @see SLS_GenericController::setMetaTitle
	 * @see SLS_GenericController::setMetaAuthor
	 * @see SLS_GenericController::setMetaCopyright
	 * @see SLS_GenericController::setMetaKeywords
	 * @see SLS_GenericController::setMetaDescription
	 * @since 1.0
	 */
	protected function setMetaRobots($robot, $incremental=false)
	{
		if ($incremental === false)
			$this->_metas['robots'] = $robot;
		else
			$this->_metas['robots'] .= " ".$robot;
	}
	
	/**
	 * Alias of SLS_Generic::redirect()
	 *
	 * @access protected	 
	 * @param string $queryString the querystring to redirect
	 * @see SLS_Generic::redirect()
	 * @since 1.0
	 */
	protected function redirect($queryString)
	{
		$this->_generic->redirect($queryString);
	}
	
	/**
	 * Alias of SLS_Generic::useModel()
	 * 
	 * @access protected
	 * @param string $modelName the model to use
	 * @param string $db the alias of the db on which we can find the model
	 * @param string $side force the side
	 * @see SLS_Generic::useModel()
	 * @since 1.0 
	 */
	protected function useModel($model,$db="",$side="")
	{
		$this->_generic->useModel($model,$db,$side);
	}
	
	/**
	 * Alias of SLS_Generic::forward()
	 *
	 * @access protected
	 * @param string $controller
	 * @param string $action
	 * @param array $more
	 * @param string $lang
	 * @since 1.0.1
	 */
	protected function forward($controller, $action, $more=array(), $lang=false)
	{
		$this->_generic->forward($controller, $action, $more, $lang);
	}
	
	/**
	 * Forward on 302
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_302($side="")
	{
		$this->forward("Default", "MaintenanceError");
	}
	
	/**
	 * Forward on 307
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_307($side="")
	{
		$this->forward("Default", "TemporaryRedirectError");
	}
	
	/**
	 * Forward on 400
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_400($side="")
	{
		$this->forward("Default", "BadRequestError");
	}
	
	/**
	 * Forward on 401
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_401($side="")
	{
		$this->forward("Default", "AuthorizationError");
	}
	
	/**
	 * Forward on 403
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_403($side="")
	{
		$this->forward("Default", "ForbiddenError");
	}
	
	/**
	 * Forward on 404
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_404($side="")
	{
		$this->forward("Default", "UrlError");
	}
	
	/**
	 * Forward on 500
	 * 
	 * @access public
	 * @since 1.1
	 */
	public function e_500($side="")
	{
		$this->forward("Default", "InternalServerError");
	}
	
	/**
	 * Alias to SLS_Generic::Dispatch()
	 * 	 
	 * @param string $controller the generic controller
	 * @param string $action the generic action
	 * @param array $http set new params for $_GET and $_POST 
	 * <code>
	 * array(
	 * 		"POST" 	=> array("key" => "value"), 
	 * 		"GET" 	=> array("key" => "value")
	 * )
	 * </code>
	 * @see SLS_Generic::Dispatch()
	 * @since 1.0
	 */
	protected function dispatch($controller, $action, $http)
	{
		$this->_generic->dispatch($controller, $action, $http);
	}
	
	/**
	 * Alias of SLS_Generic::goDirectTo()
	 *
	 * @access protected
	 * @param string $controller the generic controller
	 * @param string $action the generic action	 
	 * @param array $more arguments 
	 * <code>
	 * array('key1' => 'value1', 'key2' => 'value2', .., 'keyN' => '..')
	 * </code>
	 * You can set null to set no arguments or false to have an incomplete url like http://www.example.com/Controller/Action
	 * @param string $lang the lang if you want to switch language in the same time
	 * @since 1.0
	 */
	protected function goDirectTo($controller, $action, $more=array(), $lang=false)
	{
		$this->_generic->goDirectTo($controller, $action, $more, $lang);
	}
	
	/**
	 * Format Errors
	 *
	 * @access protected
	 * @param array $errors list of errors <code>array("error1", "error2")</code>
	 * @param SLS_XMLToolbox $currentXML the current xml
	 * @return SLS_XMLToolbox $currentXML the current xml modified
	 * @since 1.0
	 */
	protected function insertErrors($errors, $currentXML)
	{
		if (!empty($errors))
		{
			$currentXML->startTag("errors");
				foreach ($errors as $error)
					$currentXML->addFullTag("error", $error, true);
			$currentXML->endTag("errors");
		}
		return $currentXML;
	}
}
?>