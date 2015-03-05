<?php
/**
 * Front Controller
 * 
 * @author Florian Collot
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Controllers.Core 
 * @since 1.0
 */
class SLS_FrontController
{
	private static $_instance;
	private $_httpRequest;
	private $_lang;
	private $_controllerXML;
	private $_generic;
	private $_cache;
	private $_langController;
	private $_controller;
	private $_langScontroller;
	private $_scontroller;
	private $_runningController;
	private $_xml;
	private $_lastSide;
	private $_includePathControllers;
	private $_defaultClassName;
	
	/**
	 * Constructor
	 *
	 * @access private	 
	 * @since 1.0
	 */
	private function __construct()
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_cache = $this->_generic->getObjectCache();
		$this->_httpRequest = $this->_generic->getObjectHttpRequest();
		$this->_lang = $this->_generic->getObjectLang();		
		$this->_controllerXML = $this->_generic->getControllersXML();
		$this->parseUrl();
		$this->_lastSide = $this->_generic->getObjectSession()->getParam('lastSide');
		$this->_includePathControllers = (!empty($this->_lastSide) && $this->_lastSide == 'sls') ? "coreActionsControllers" : "actionsControllers";
		$this->_defaultClassName = (!empty($this->_lastSide) && $this->_lastSide == 'sls') ? "SLS_Default" : "Default";
		(!empty($this->_lastSide) && $this->_lastSide == 'sls') ? $this->_lang = new SLS_Lang() : "";
	}

	/**
	 * Singleton's pattern
	 *
	 * @access public static	 
	 * @return SLS_FrontController $instance the self reference
	 * @since 1.0
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new SLS_FrontController();
		}
		return self::$_instance;
	}

	/**
	 * URL's parsing to know the wanted couple Controller/Action
	 *
	 * @access public
	 * @since 1.0
	 */
	public function parseUrl()
	{
		$this->_langController = $this->_httpRequest->getParam('mode');
		$this->_langScontroller = $this->_httpRequest->getParam('smode');
		
		if (empty($this->_langController) && $this->_generic->getSiteConfig('isInstall') == false) 
		{
			$this->_controller = "Home";
			$this->_generic->setGenericControllerName("Home");
			$this->_scontroller = "Index";
			$this->_generic->setGenericScontrollerName("Index");
			$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='Home']/scontrollers/scontroller[@name='Index']/@id")));
			$scontrollerTpl = array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']/@tpl"));
			$controllerTpl 	= array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$this->_controller."']/@tpl"));
			if (!empty($scontrollerTpl))
				$this->_generic->setCurrentTpl($scontrollerTpl);
			else if (!empty($controllerTpl))
				$this->_generic->setCurrentTpl($controllerTpl);			
		}
		else if($this->_generic->getSiteConfig('isInstall') == true)
		{
			$this->_generic->setSide('sls');
			$controller = $this->_generic->getCoreConfig('installation/controller/name', 'sls');
			$scontroller = $this->_generic->getCoreConfig('installation/scontroller/name', 'sls');
			if (empty($controller) || empty($scontroller))
			{
				if (empty($this->_langController) && empty($this->_langScontroller))
				{
					$this->_controller = "SLS_Init";			
					$this->_generic->setGenericControllerName("SLS_Init");
					$this->_scontroller = "Index";			
					$this->_generic->setGenericScontrollerName("Index");
					$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='SLS_Init' and @side='sls']/scontrollers/scontroller[@name='Index']/@id")));					
				}
				else
				{
					if ($this->_langController == "Initialization" || $this->_langController == "SLS_Init")
					{
						$this->_controller = "SLS_Init";
						$this->_generic->setGenericControllerName("SLS_Init");
						$controllerXml = $this->_generic->getControllersXML();
						$this->_scontroller = $controllerXml->getTag("//controllers/controller[@name='SLS_Init']/scontrollers/scontroller[scontrollerLangs/scontrollerLang='".$this->_langScontroller."']/@name");			
						$this->_generic->setGenericScontrollerName($this->_scontroller);
						$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='SLS_Init' and @side='sls']/scontrollers/scontroller[@name='".$this->_scontroller."']/@id")));
					}
				}
			}
			else 
			{
				$this->_controller = $controller;
				$this->_generic->setGenericControllerName($controller);
				$this->_scontroller = $scontroller;
				$this->_generic->setGenericScontrollerName($scontroller);
				$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$scontroller."']/@id")));
			}			
		}
	}
	
	/**
	 * Dispatch the controller and the action within application's reloading	 
	 *
	 * @access public
	 * @param string $controller the generic name of the wanted controller
	 * @param string $action the generic name of the wanted action	 
	 * @param array $http set new params for $_GET and $_POST 
	 * <code>
	 * array(
	 * 		"POST" 	=> array("key" => "value"), 
	 * 		"GET" 	=> array("key" => "value")
	 * )
	 * </code>
	 * @since 1.0
	 */
	public function dispatch($controller,$action,$http=array("post"=>array(),"get"=>array()))
	{	
		// Set controller & action
		$generics = $this->_generic->getTranslatedController($controller, $action);
		$this->_generic->setGenericControllerName($controller);
		$this->_generic->setGenericScontrollerName($action);
		$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."']/@id")));
		$this->_langController = $generics['controller'];
		$this->_langScontroller = $generics['scontroller'];
		$this->_controller = $controller;		
		$this->_scontroller = $action;	

		if (array_key_exists("POST", $http) && array_key_exists("GET", $http))
		{
			if (!empty($http['POST']))
				$_POST = $http['POST'];
			if (!empty($http['GET']))
				$_GET = $http['GET'];
		}
		$this->_generic->setBufferXML("<?xml version=\"1.0\" encoding=\"UTF-8\"?><root></root>",false);
		
		// We return into the mapping of the application		
		$this->loadController(); 
	}

	/**
	 * Recover rewrite params to find the controller' class and to call the action's function
	 *
	 * @access public
	 * @since 1.0
	 */
	public function loadController()
	{		
		// Timer checkpoint
		$this->_generic->_time_checkpoint = microtime(true);
		
		// Recover Controller & Action
		$this->getControllerScontroller();
		
		// Check if current asked language is disabled		
		if ($this->_generic->getSiteConfig('isInstall') == 0 && $this->_generic->getSide() == "user")
		{			
			if (!$this->_lang->isEnabledLang($this->_lang->getLang()))		
				$this->_generic->switchLang($this->_generic->getSiteConfig("defaultLang"),$this->_generic->getGenericControllerName(),$this->_generic->getGenericScontrollerName());			
		}
		// Save in session current informations		
		$session = $this->_generic->getObjectSession();
		$session->setParam("current_controller_generic",$this->_generic->getGenericControllerName());
		$session->setParam("current_controller_translated",array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$this->_generic->getGenericControllerName()."']/controllerLangs/controllerLang[@lang='".$this->_lang->getLang()."']")));
		$session->setParam("current_action_generic",$this->_generic->getGenericScontrollerName());
		$session->setParam("current_action_translated",$this->_controllerXML->getTag("//controllers/controller[@name='".$this->_generic->getGenericControllerName()."']/scontrollers/scontroller[@name='".$this->_generic->getGenericScontrollerName()."']/scontrollerLangs/scontrollerLang[@lang='".$this->_lang->getLang()."']"));
		$session->setParam("current_more",SLS_String::substrAfterFirstDelimiter($_SERVER["REQUEST_URI"],"/".$this->_langController."/".$this->_langScontroller));
		
		// Specific case of Default Controller and actionError
		if (empty($this->_controller))
			$this->_generic->setSide("user");
		
		// If we are on user side and not on default action
		if ($this->_generic->getSide() == "user" && !empty($_SESSION['current_controller_generic']) && $_SESSION['current_controller_generic'] != "Default")
		{
			// Check if we must redirect on a specific domain binded to current lang
			$domainLang = $this->_generic->getSiteXML()->getTag("//configs/domainName/domain[. = '".$_SERVER['HTTP_HOST']."']/@lang");
			$domainAlias = $this->_generic->getSiteXML()->getTag("//configs/domainName/domain[. = '".$_SERVER['HTTP_HOST']."']/@alias");
			$currentLangDomain = $this->_generic->getSiteXML()->getTag("//configs/domainName/domain[@lang = '".$this->_lang->getLang()."']/@alias");

			if (!empty($_SESSION['current_controller_generic']) && !empty($_SESSION['current_action_generic']) && (!empty($currentLangDomain)) && $currentLangDomain != $domainAlias && $this->_lang->isEnabledLang($domainLang))
				$this->_generic->forward($_SESSION['current_controller_generic'],$_SESSION['current_action_generic'],array(),$domainLang,$domainAlias);
		}
		
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
			$actionCacheResponsive	= $cacheOptions[2];
			$actionCacheExpiration 	= $cacheOptions[3];
			
			// Full HTML cached
			if ($actionCacheScope == "full" && false !== ($actionCached = $this->_cache->getCacheFull($actionCacheExpiration,$actionCacheVisibility,$actionCacheResponsive)))
			{
				$this->_generic->_time_checkpoint = microtime(true);
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Cache (Full): Executing Action","","Controller Action");				
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_start),"Process finished","","Render");
				if (PHP_SAPI !== 'cli')
					header('Content-type: text/html; charset='.$this->_generic->getSiteConfig("defaultCharset"));
				echo $actionCached;
				die();
			}
		}
		
		// Load file language of the current controller
		$this->_lang->loadControllerLang($this->_generic->getGenericControllerName());
		
		// Load file language of the current action
		$this->_lang->loadActionLang($this->_generic->getGenericControllerName(),$this->_generic->getGenericScontrollerName());
		
		// Load statics controllers
		include_once($this->_generic->getPathConfig("coreControllers")."SLS_FrontStatic.controller.php");
		include_once($this->_generic->getPathConfig("coreControllers")."SLS_LoadStatics.controller.php");
		$staticsClasses = new SLS_LoadStaticsControllers();		
		$this->_generic->setBufferXML($staticsClasses->getXML(), true, "//root");
		
		// Load components controllers
		include_once($this->_generic->getPathConfig("coreControllers")."SLS_FrontComponent.controller.php");
		include_once($this->_generic->getPathConfig("coreControllers")."SLS_LoadComponents.controller.php");
		$componentsClasses = new SLS_LoadComponentsControllers();		
		$this->_generic->setBufferXML($componentsClasses->getXML(), true, "//root");
		
		// If the directory exists
		$path = ($this->_generic->getSide() == 'user') ? 'actionsControllers' : 'coreActionsControllers';
		if (is_dir($this->_generic->getPathConfig($path).$this->_controller))
		{
			$this->loadScontroller($path);
			
			// Set Session Param to manage Errors
			if (!empty($this->_scontroller) && !empty($this->_controller) && $this->_controller != $this->_defaultClassName)
				$this->_generic->getObjectMemberSession()->setTrackingPage();	
		}
		// Else, include the Default Controller and call the actionError
		else
		{
			if (get_class($this->_runningController) != $this->_defaultClassName.'UrlError')
			{
				include_once($this->_generic->getPathConfig($this->_includePathControllers).$this->_defaultClassName."/UrlError.controller.php");
				eval('$this->_runningController = new '.$this->_defaultClassName.'UrlError();');
			}
			$url = strtolower(SLS_String::substrBeforeFirstDelimiter($_SERVER['SERVER_PROTOCOL'],'/')).'://'.$_SERVER['HTTP_HOST'].'';
			foreach($this->_generic->getObjectHttpRequest()->getParams() as $key => $value)				
				$url .= ((is_string($value)) ? ((!in_array($key,array('mode','smode'))) ? '/'.$key : '').'/'.$value : '');
			$this->_generic->setGenericControllerName($this->_defaultClassName);
			$this->_generic->setGenericScontrollerName("UrlError");
			$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$this->_defaultClassName."']/scontrollers/scontroller[@name='UrlError']/@id")));			
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Resolve Mapping (".$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().")","Url: ".$url.'.'.$this->_generic->getSiteConfig("defaultExtension")."|n|".str_replace("[","|t| |t|[",(string)print_r($this->_generic->getObjectHttpRequest()->getParams(),true)),"Controller Front");
			$this->_generic->_time_checkpoint = microtime(true);			
			$this->_runningController->init();
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Init","","Controller Init");
			$this->_generic->_time_checkpoint = microtime(true);			
			$this->_runningController->action();
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Action","","Controller Action");
		}
		
		// Get the XML from the current controller
		$this->_xml = $this->_runningController->getFinalXML();		
	}

	/**
	 * Call the action's function
	 * 
	 * @access public
	 * @param string $path the controllers path
	 * @since 1.0
	 */
	public function loadScontroller($path)
	{		
		// If the file exist
		if (is_file($this->_generic->getPathConfig($path).$this->_generic->getGenericControllerName()."/".$this->_scontroller.".controller.php"))
		{
			$controllerName = $this->_generic->getGenericControllerName().$this->_scontroller;
			if (is_file($this->_generic->getPathConfig($path)."__site.protected.php"))
				include_once($this->_generic->getPathConfig($path)."__site.protected.php");
			else 
				SLS_Tracing::addTrace(new Exception("A generic File is missing '__site.protected.php' for the current Action. Controller: '".$this->_generic->getGenericControllerName()."' ; Action : '".$this->_scontroller."'"));			
			
			if (is_file($this->_generic->getPathConfig($path).$this->_generic->getGenericControllerName()."/"."__".$this->_generic->getGenericControllerName().".protected.php"))
				include_once($this->_generic->getPathConfig($path).$this->_generic->getGenericControllerName()."/"."__".$this->_generic->getGenericControllerName().".protected.php");
			else
				SLS_Tracing::addTrace(new Exception("A generic File is missing '__".$this->_generic->getGenericControllerName().".protected.php' for the current Action. Controller: '".$this->_generic->getGenericControllerName()."' ; Action : '".$this->_scontroller."'"));			
			
			$url = strtolower(SLS_String::substrBeforeFirstDelimiter($_SERVER['SERVER_PROTOCOL'],'/')).'://'.$_SERVER['HTTP_HOST'].'';
			foreach($this->_generic->getObjectHttpRequest()->getParams() as $key => $value)				
				$url .= ((is_string($value)) ? ((!in_array($key,array('mode','smode'))) ? '/'.$key : '').'/'.$value : '');
			
			if (PHP_SAPI !== 'cli')
			{
				$robots = $this->_generic->getCoreXML('metas')->getTag("//sls_configs/action[@id='".$this->_generic->getActionId()."']/robots");
				header("X-Robots-Tag: ".$robots, true);
			}
				
			include_once($this->_generic->getPathConfig($path).$this->_generic->getGenericControllerName()."/".$this->_scontroller.".controller.php");
			$this->_runningController = new $controllerName();
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Resolve Mapping (".$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().")","Url: ".$url.'.'.$this->_generic->getSiteConfig("defaultExtension")."|n|".str_replace("[","|t| |t|[",(string)print_r($this->_generic->getObjectHttpRequest()->getParams(),true)),"Controller Front");
						
			// Sls cached enabled and Action cache enabled ?
			$runAction = true;
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
				if ($actionCacheScope == "partial" && false !== ($actionCached = $this->_cache->getCachePartial($actionCacheExpiration,"action","action_".SLS_String::substrAfterFirstDelimiter($this->_generic->getActionId(),"a_"),$actionCacheVisibility,$actionCacheResponsive)))
					$runAction = false;
			}
			
			if ($runAction)
			{
				$this->_generic->_time_checkpoint = microtime(true);		
				$this->_runningController->init();
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Init","","Controller Init");
				$this->_generic->_time_checkpoint = microtime(true);
				$this->_runningController->action();
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Executing Action","","Controller Action");
			}
		}
		
		// Reset sls_db_infos in session
		$this->_generic->getObjectSession()->delParam("sls_db_infos");
	}

	/**
	 * Recover generic name of the controller class and it's action function according to what has been spent in any language
	 * 
	 * @access public
	 * @since 1.0
	 */
	public function getControllerScontroller() 
	{
		// If we haven't again found a generic controller and scontroller
		if ($this->_generic->getGenericControllerName() == "" && $this->_generic->getGenericScontrollerName() == "")
		{
			$actualLang = $this->_lang->getLang();
			$arrayLangs = $this->_lang->getSiteLangs();
			$tmpController = "";
			
			// Try to recover controller & action in the current language of the client
			$this->_controller = $this->_controllerXML->getTag("//controllers/controller[controllerLangs[translate(controllerLang[@lang='".$actualLang."'],'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langController)."']]/@name");
			$this->_scontroller = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[scontrollerLangs[translate(scontrollerLang[@lang='".$actualLang."'],'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langScontroller)."']]/@name");
						
			// If it was not found in this language, seek in others
			if (empty($this->_controller) || (empty($this->_scontroller) && !empty($this->_langScontroller)))
			{
				if (!in_array('en', $arrayLangs))
					array_push($arrayLangs, 'en');			
				
				foreach ($arrayLangs as $lang)
				{
					if ($actualLang == 'en' || $lang != $actualLang)
					{	
						$this->_controller = $this->_controllerXML->getTag("//controllers/controller[controllerLangs[translate(controllerLang[@lang='".$lang."'],'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langController)."']]/@name");
						
						if (!empty($this->_controller))
						{
							$tmpController = $this->_controller; // Keep a copy of the controller found
							$this->_scontroller = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[scontrollerLangs[translate(scontrollerLang[@lang='".$lang."'],'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langScontroller)."']]/@name");
							if (!empty($this->_scontroller))
							{
								$this->_generic->setGenericControllerName($this->_controller);
								$this->_generic->setGenericScontrollerName($this->_scontroller);	
								$this->_generic->setActionId($this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']/@id"));								
								$this->_lang->setLang($lang);								
								break;
							}
						}
					}
				}				
			}
			
			// If controller is empty, try to re set-it with the copy
			if (empty($this->_controller))
				$this->_controller = $tmpController;
			
			// If no language match these names, seek into generic names
			if (empty($this->_controller))
			{
				$this->_controller = $this->_controllerXML->getTag("//controllers/controller[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langController)."']/@name");
				
				// If controller has been found whereas we are into sls side, destroy the controller
				if (!empty($this->_controller) && $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/@side") == 'sls')
					$this->_controller = "";
			}
			
			// If controller has been found
			if (!empty($this->_controller))
			{
				// If accessing only by controller
				if (empty($this->_langScontroller) && empty($this->_scontroller))
				{	
					$cur_lang = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/controllerLangs/controllerLang[translate(.,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langController)."']/@lang");					
					$defaultAction = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@default='1']/@name");
					
					if (!empty($defaultAction))
						$this->_generic->forward($this->_controller,$defaultAction,array(),(in_array($cur_lang,$arrayLangs)) ? $cur_lang : false);
				}
				
				// Seek the action
				if (empty($this->_scontroller) && $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/@side") == 'user')
					$this->_scontroller = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='".strtolower($this->_langScontroller)."']/@name");
				// If the action hasn't been found
				if (empty($this->_scontroller))	
				{
					$this->_controller = $this->_defaultClassName;
					$this->_scontroller = "UrlError";
				}
			}
			else
			{
				$this->_controller = $this->_defaultClassName;
				$this->_scontroller = "UrlError";
			}
			
			// Check disabled & forbidden actions			
			$this->isDisabled();
			$this->isForbidden();
			
			// Set the generic names
			$this->_generic->setGenericControllerName($this->_controller);
			$this->_generic->setGenericScontrollerName($this->_scontroller);
			$this->_generic->setActionId($this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']/@id"));
			
			// Check maintenance
			$this->isMaintenance();
			
			// Set the template for this action
			$scontrollerTpl = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']/@tpl");
			$controllerTpl 	= $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/@tpl");
			if (!empty($scontrollerTpl))
				$this->_generic->setCurrentTpl($scontrollerTpl);
			else if (!empty($controllerTpl))
				$this->_generic->setCurrentTpl($controllerTpl);			
		}
		else
		{
			$this->isMaintenance();
			
			// Set the template for this action
			$scontrollerTpl = $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']/@tpl");
			$controllerTpl 	= $this->_controllerXML->getTag("//controllers/controller[@name='".$this->_controller."']/@tpl");
			if (!empty($scontrollerTpl))
				$this->_generic->setCurrentTpl($scontrollerTpl);
			else if (!empty($controllerTpl))
				$this->_generic->setCurrentTpl($controllerTpl);
		}
	}
	
	/**
	 * Check if the website is in maintenance
	 * 
	 */
	private function isMaintenance()
	{		
		if ($this->_generic->getSide() == "user" && $this->_controller != $this->_defaultClassName && $this->_generic->isMaintenance() && $this->_generic->getObjectSession()->getParam("SLS_SESSION_VALID_".substr(substr(sha1($this->_generic->getSiteConfig("privateKey")),12,31).substr(sha1($this->_generic->getSiteConfig("privateKey")),4,11),6)) != 'true')
		{
			$this->_controller = $this->_defaultClassName;
			$this->_scontroller = "MaintenanceError";
			$this->_generic->setGenericControllerName($this->_defaultClassName);
			$this->_generic->setGenericScontrollerName("MaintenanceError");
			$this->_generic->setActionId($this->_controllerXML->getTag("//controllers/controller[@name='".$this->_defaultClassName."']/scontrollers/scontroller[@name='MaintenanceError']/@id"));
		}
		
		return false;
	}
	
	/**
	 * Check if the current action is disabled
	 * 
	 */
	private function isDisabled()
	{
		$result = array_shift($this->_controllerXML->getTagsAttributes("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']",array("disable")));
		
		if (!empty($result) && !empty($result["attributes"][0]["value"]) && $result["attributes"][0]["value"] == "1" && $this->_generic->getObjectSession()->getParam("SLS_SESSION_VALID_".substr(substr(sha1($this->_generic->getSiteConfig("privateKey")),12,31).substr(sha1($this->_generic->getSiteConfig("privateKey")),4,11),6)) != 'true')
		{
			$this->_controller = $this->_defaultClassName;
			$this->_scontroller = "TemporaryRedirectError";
			$this->_generic->setGenericControllerName($this->_defaultClassName);
			$this->_generic->setGenericScontrollerName("TemporaryRedirectError");
			$this->_generic->setActionId(array_shift($this->_controllerXML->getTags("//controllers/controller[@name='".$this->_defaultClassName."']/scontrollers/scontroller[@name='TemporaryRedirectError']/@id")));
		}
		else
			return false;
	}
	
	/**
	 * Check if the current action is forbidden on the current domain
	 * 
	 */
	private function isForbidden()
	{
		// Aliases
		$siteXML = $this->_generic->getSiteXML();
		$currentDomainAlias = "";
		
		// Current domain alias
		$result = array_shift($siteXML->getTagsAttributes("//configs/domainName/domain[.='".$_SERVER['HTTP_HOST']."']",array("alias")));
		if (!empty($result))
			$currentDomainAlias = $result["attributes"][0]["value"];
		
		// Domain aliases forbidden
		$result = array_shift($this->_controllerXML->getTagsAttributes("//controllers/controller[@name='".$this->_controller."']/scontrollers/scontroller[@name='".$this->_scontroller."']",array("domains")));
		
		// If current action is forbidden in this domain
		if (!empty($result) && !empty($result["attributes"][0]["value"]) && (in_array($currentDomainAlias,explode(",",$result["attributes"][0]["value"]))))
		{
			$this->_controller = $this->_defaultClassName;
			$this->_scontroller = "ForbiddenError";
			$this->_generic->setGenericControllerName($this->_defaultClassName);
			$this->_generic->setGenericScontrollerName("ForbiddenError");
			$this->_generic->setActionId($this->_controllerXML->getTag("//controllers/controller[@name='".$this->_defaultClassName."']/scontrollers/scontroller[@name='ForbiddenError']/@id"));
		}
		else
			return false;
	}
	
	/**
	 * Get the XML of the current controller
	 *
	 * @access public
	 * @return string $xml the xml of the current controller
	 * @since 1.0
	 */
	public function getXML()
	{		
		return $this->_xml;
	}
}
?>