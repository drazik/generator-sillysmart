<?php
function __autoload($className)
{
	$cache = SLS_Generic::getInstance()->getCache();
	if(($path = array_search($className, $cache['Objects']['core'])) !== false)
	{
		include_once($path);
		return;
	}
	if (SLS_Generic::getInstance()->getSide() == 'user' && ($path = array_search($className, $cache['Objects']['user'])) !== false)
	{
		include_once($path);
		return;
	}
	SLS_Tracing::addTrace(new Exception("The Class ".$className." was not found"));	
}
function t($tabs=1)
{
	$output = '';
	for($i=0 ; $i<intval($tabs) ; $i++)
		$output .= "\t";
	return $output;
}
function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
	if ($errno == 2)
		throw new Exception($errstr,$errno);	
}
set_error_handler('errorHandler');

/**
 * Generic Class - Used eveywhere in the application
 * 
 * @author Laurent Bientz
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics
 * @since 1.0  
 */
class SLS_Generic 
{
	private static $_instance;
	private $_genericPath = "Sls/Generics";
	private $_configSecurePath = "Sls/Configs/Site";
	private $_configDeployement = "Sls/Deployement/Configs/Site";
	private $_configNonSecurePath = "Sls/Configs";
	private $_projectName;
	private $_protocol;
	private $_root;
	private $_session;
	private $_cookie;
	private $_memberSession;
	private $_httpRequest;
	private $_security;	
	private $_xml;
	private $_lang;
	private $_ajaxPageFactory;
	private $_configDb;
	private $_configSite;
	private $_configCache;
	private $_controllers;	
	private $_paths;
	private $_project;
	private $_slsCoreXml = array();
	private $_slsPluginXml = array();
	private $_mails;
	private $_cache;
	private $_genericController;
	private $_controllerId;
	private $_actionId;
	private $_genericScontroller;
	private $_translatedControllerName = array();
	private $_translatedScontrollerName = array();
	private $_bufferXML = '<?xml version="1.0" encoding="UTF-8"?><root></root>';
	private $_isProd;
	private $_isCache;
	private $_isMaintenance;
	private $_isMonitoring;
	private $_side = 'user';
	private $_dataCache = array();
	private $_linksRegistred = array();
	private $_initSillySmart = false;
	private $_isRemote = false;
	private $_currentTpl = "__default";
	public $_time_start = 0;
	public $_time_checkpoint = 0;
	public $_view_custom_vars = array();	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */	
	public function __construct(){}
	
	/**
	 * Load the framework
	 *
	 * @access public
	 * @param string $root
	 * @param bool $remote true if you want to access sillysmart in a isolated way like web-services, else false (default)
	 * @return object $this the current instance
	 * @since 1.0
	 */
	public function loadFramework($root="",$remote=false) 
	{
		$this->_time_start = microtime(true);
		
		$this->_root = $root;
		$this->_isRemote = $remote;
		$this->_dataCache['Mail'] = array();
		$this->_dataCache['Site'] = array();
		$this->_dataCache['Dbs'] = array();
		$this->_dataCache['Paths'] = array();
		$this->_dataCache['Project'] = array();
		$this->_dataCache['Objects']['user'] = array();
		$this->_dataCache['Objects']['core'] = array();
		
		// If first use, deploy site.xml
		if (!is_file($this->_root.$this->_configSecurePath."/site.xml"))
				copy($this->_root.$this->_configDeployement."/site.xml", $this->_root.$this->_configSecurePath."/site.xml");
		
		// Load Generic Objects
		$this->includeGenericsObjects();
		
		// Open file configs of Paths		
		$pathsHandle = file_get_contents($this->_root.$this->_configSecurePath."/paths.xml");
		$this->_paths = new SLS_XMLToolbox($pathsHandle);	
		
		// Open file configs of Database		
		$dbHandle = file_get_contents($this->_root.$this->_configSecurePath."/db.xml");
		$this->_configDb = new SLS_XMLToolbox($dbHandle);
			
		// Open file configs of Site		
		$siteHandle = file_get_contents($this->_root.$this->_configSecurePath."/site.xml");
		$this->_configSite = new SLS_XMLToolbox($siteHandle);
		
		// Open file configs of Cache		
		$cacheHandle = file_get_contents($this->_root.$this->_configSecurePath."/cache.xml");
		$this->_configCache = new SLS_XMLToolbox($cacheHandle);
		
		// Open file configs of Controllers		
		$controllersHandle = file_get_contents($this->_root.$this->_configSecurePath."/controllers.xml");
		$this->_controllers = new SLS_XMLToolbox($controllersHandle);	
		
		// Open file configs of Mail Configs		
		$mailsHandle = file_get_contents($this->_root.$this->_configSecurePath."/mail.xml");
		$this->_mails = new SLS_XMLToolbox($mailsHandle);		
		
		// Open file configs of Sls
		$slsHandle = file_get_contents($this->_root.$this->_configNonSecurePath."/Sls/sls.xml");
		$this->_slsCoreXml['sls'] = new SLS_XMLToolbox($slsHandle);	
		
		// Open file configs of Metas
		$slsHandle = file_get_contents($this->_root.$this->_configNonSecurePath."/Sls/metas.xml");
		$this->_slsCoreXml['metas'] = new SLS_XMLToolbox($slsHandle);	
		
		// Default timezone
		date_default_timezone_set(($this->getSiteConfig("defaultTimezone") == "") ? "Europe/Paris" : $this->getSiteConfig("defaultTimezone"));
		
		// Check if it's your first time on SillySmart		
		if ($this->getCoreConfig('installation/step', 'sls') != -1)
			$this->_initSillySmart = true;
		if ($this->_initSillySmart)
		{			
			$domainName = $_SERVER['HTTP_HOST'].SLS_String::substrBeforeLastDelimiter($_SERVER['PHP_SELF'],"/");
			$this->_side = "sls";
			$this->_dataCache['Site']['projectName'] = "SillySmart Installation Wizard";
			$this->_dataCache['Site']['defaultLoadStaticsJavascript'] = 1;
			$this->_dataCache['Site']['defaultLoadDynsJavascript'] = 1;
			$this->_dataCache['Site']['defaultBuildConfigsJsVars'] = 1;
			$this->_dataCache['Site']['defaultMultilanguageJavascript'] = 1;
			$this->_dataCache['Site']['isInstall'] = 1;
			$this->_dataCache['Site']['domainName'] = $domainName;	
			$this->_dataCache['Site']['protocol'] = ($_SERVER['HTTPS'] == "on") ? "https" : "http";
		}
		else
		{
			// Flush deprecated logs
			$this->flushLogs();
			
			$this->_dataCache['Site']['isInstall'] = 0;
		}
		
		$GLOBALS['PROJECT_NAME'] = strtoupper($this->getSiteConfig("projectName"));				
		$this->loadGenericsObjects();
		$this->includeCoreControllers();
		$this->includeCoreModels();
		
		return $this;
	}

	/**
	 * Singleton
	 * 
	 * @access public static	 	 
	 * @return SLS_Generic $instance SLS_Generic instance
	 * @since 1.0
	 */
	public static function getInstance() 
	{
		if (is_null(self::$_instance))		
			self::$_instance = new SLS_Generic();		
		return self::$_instance;
	}
	
	/**
	 * Return prefix of the relative path
	 *
	 * @access public
	 * @return string $root the root path
	 * @since 1.0
	 */
	public function getRoot()
	{
		return $this->_root;
	}
	
	/**
	 * Include of the generics files	 
	 *
	 * @access private
	 * @since 1.0
	 */
	private function includeGenericsObjects()
	{
		if (!is_file($this->_root.$this->_configSecurePath."/cache.xml"))
		{
			if (touch($this->_root.$this->_configSecurePath."/cache.xml") === false)
				throw new Exception("You have not enougth right on the Directory ".$this->_root.$this->_configSecurePath." to create a file");
			else
				file_put_contents($this->_root.$this->_configSecurePath."/cache.xml", "<?xml version=\"1.0\" encoding=\"UTF-8\"?><statics></statics>",LOCK_EX);
		}
		$cacheObjects = file_get_contents($this->_root.$this->_configSecurePath."/cache.xml");
		
		$this->_isCache = (array_shift($this->getXmlTags(file_get_contents($this->_root.$this->_configSecurePath."/site.xml"), "//configs/activeCache")) == 0) ? false : true;
		
		// Badformed XML
		if (!$this->isValidXML($cacheObjects))
		{
			file_put_contents($this->_root.$this->_configSecurePath."/cache.xml", "<?xml version=\"1.0\" encoding=\"UTF-8\"?><statics></statics>",LOCK_EX);
			$xml = "<objects>";
		
			$handle = opendir($this->_root.$this->_genericPath);
			while (false !== ($dir = readdir($handle))) 
			{
				if (is_dir($this->_root.$this->_genericPath."/".$dir) && substr($dir, 0, 1) != ".")
				{
					$files = $this->recursiveReadDir($this->_root.$this->_genericPath."/".$dir, array(), array("php"));
					foreach ($files as $file)
					{
						$xml .="<object><file>";
						$this->_dataCache['Objects']['core'][$file] = array_shift(explode('.', array_pop(explode("/", $file))));
						$xml .= "<![CDATA[".$file."]]></file></object>";								
					}
					
				}
			}
			closedir($handle);		
	    	$xml .= "</objects>";
	    	$this->saveCacheXML($xml);
	    	$cacheObjects = file_get_contents($this->_root.$this->_configSecurePath."/cache.xml");
		}
		
		if ($this->isCache())
		{	
			// GetTags
			$objects = $this->getXmlTags($cacheObjects, "//statics/objects/object/file");			
			foreach ($objects as $object)			
				$this->_dataCache['Objects']['core'][$this->root.$object] = array_shift(explode('.', array_pop(explode("/", $object))));			
		}
		else 
		{
			$xml = "<objects>";
		
			$handle = opendir($this->_root.$this->_genericPath);
			while (false !== ($dir = readdir($handle))) 
			{
				if (is_dir($this->_root.$this->_genericPath."/".$dir) && substr($dir, 0, 1) != ".")
				{
					$files = $this->recursiveReadDir($this->_root.$this->_genericPath."/".$dir, array(), array("php"));
					foreach ($files as $file)
					{
						$xml .="<object><file>";
						$this->_dataCache['Objects']['core'][$file] = array_shift(explode('.', array_pop(explode("/", $file))));
						$xml .= "<![CDATA[".$file."]]></file></object>";
								
					}
					
				}
			}
			closedir($handle);		
	    	$xml .= "</objects>";
	    	$this->saveCacheXML($xml);
		}
	}	
	
	/**
	 * Save the cache file if the cache is enabled	 
	 *
	 * @access public
	 * @param string $xml the xml to save
	 * @since 1.0
	 */
	public function saveCacheXML($xml)
	{
		
		$cacheObjects = file_get_contents($this->_root.$this->_configSecurePath."/cache.xml");
		$writeXML = new SLS_XMLToolbox($cacheObjects);
		$writeXML->overwriteTags("//statics", $xml);		
		try {
			$xml = simplexml_load_string($writeXML->getXML());
			$writeXML->saveXML($this->_root.$this->_configSecurePath."/cache.xml");
		}
		catch (Exception $e) {} 
	}
	
	/**
	 * Include the user objects	 
	 *
	 * @access private
	 * @since 1.0
	 */
	private function includeUserObjects()
	{
		$xmlCache = new SLS_XMLToolbox(file_get_contents($this->_configSecurePath."/cache.xml"));
		if ($this->isCache())
		{
			$objects = $xmlCache->getTags("//statics/userObjects/object/file");
			// GetTags
			foreach ($objects as $object)			
				$this->_dataCache['Objects']['user'][$this->root.$object] = array_shift(explode('.', array_pop(explode("/", $object))));
		}
		else 
		{			
			$xml = "<userObjects>";
			$userGenerics = array();
			$searchExt = array('php');
			$userGenerics = $this->recursiveReadDir($this->getPathConfig("plugins"), $userGenerics, $searchExt);
			for ($i=0;$i<($count = count($userGenerics));$i++)
			{
				$xml .="<object><file>";
				$this->_dataCache['Objects']['user'][$userGenerics[$i]] = array_shift(explode('.', array_pop(explode("/", $userGenerics[$i]))));
				$xml .= "<![CDATA[".$userGenerics[$i]."]]></file></object>";
			}
			$xml .= "</userObjects>";
			$this->saveCacheXML($xml);			
		}
	}
	
	/**
	 * Permit to list with recursivity a directory
	 *
	 * @access public
	 * @param string $path 
	 * @param array $arrayFiles
	 * @param array $searchedExtension
	 * @return array
	 * @since 1.0
	 */
	public function recursiveReadDir($path, $arrayFiles=array(), $searchedExtension=array())
	{
		if (substr($path, strlen($path)-1, 1) != "/")
			$path .= "/";			
		$handle = opendir($path);
		while (($dir=readdir($handle)) !== false)
		{
			if (substr($dir, 0, 1) != ".")
			{
				if (is_dir($path.$dir))
					$arrayFiles = $this->recursiveReadDir($path.$dir, $arrayFiles, $searchedExtension);				
				elseif (empty($searchedExtension) || in_array(substr($path.$dir, strrpos($path.$dir, ".")+1), $searchedExtension)) 
					array_push($arrayFiles, $path.$dir);				
			}
		}		
		return $arrayFiles;
	}
	
	/**
	 * Include the Generic controller & the Front controller
	 *
	 * @access private
	 * @since 1.0
	 */
	private function includeCoreControllers()
	{
		include_once($this->getPathConfig("coreControllers")."SLS_GenericController.controller.php");
		include_once($this->getPathConfig("coreControllers")."SLS_FrontController.controller.php"); 
	}
	
	/**
	 * Include the Generics models	 
	 *
	 * @access private
	 * @since 1.0
	 */
	private function includeCoreModels()
	{		
		include_once($this->getPathConfig("coreModels")."SLS_FrontModel.model.php");
		include_once($this->getPathConfig("coreModels")."SLS_FrontModel.sql.php"); 
	}
	
	/**
	 * Include a model	 
	 *
	 * @access public
	 * @param string $modelName the model to use
	 * @param string $db the alias of the db on which we can find the model
	 * @param string $side force the side
	 * @return bool true if succes, else false
	 * @since 1.0
	 */
	public function useModel($modelName,$db="",$side="") 
	{
		// Check default side
		if (!empty($side))
		{
			if ($side=="sls")
				$path = "coreSls";
			else if ($side=="user")
				$path = "";
		}
		else
			$path = ($this->getSide()=='sls') ?	"coreSls" :"";
		
		// Check default db
		if (empty($db))
		{
			$result = array_shift($this->_configDb->getTagsAttribute("//dbs/db[@isDefault='true']","alias"));
			$db = $result["attribute"];
		}
		// Include model files
		if (is_file($this->getPathConfig($path.((empty($path)) ? "models" : "Models")).ucfirst($db).".".ucfirst($modelName).".model.php") and is_file($this->getPathConfig($path.((empty($path)) ? "modelsSql" : "ModelsSql")).ucfirst($db).".".ucfirst($modelName).".sql.php")) 
		{
			include_once($this->getPathConfig($path.((empty($path)) ? "models" : "Models")).ucfirst($db).".".ucfirst($modelName).".model.php");
			include_once($this->getPathConfig($path.((empty($path)) ? "modelsSql" : "ModelsSql")).ucfirst($db).".".ucfirst($modelName).".sql.php");
			
			// Check if the correct database is already set
			$sql = SLS_Sql::getInstance();			
			if (strtolower($db) != strtolower($sql->getCurrentDb()))
				$sql->changeDb($db);
			
			return true;
		}
		else 
		{
			return false;	
		}
	}
	/**
	 * Instanciate all the generics objects	 
	 *
	 * @access private
	 * @since 1.0
	 */	
	private function loadGenericsObjects() 
	{
		$this->_session = new SLS_Session($this->_isRemote);		
		$this->_lang = new SLS_Lang($this);
		$this->_cache = new SLS_Cache();
		$this->_httpRequest = new SLS_HttpRequest();
		$this->_memberSession = SLS_MemberSession::getInstance($this);		
	}
	
	/**
	 * Get the session object	 
	 *
	 * @access public
	 * @return SLS_Session $session the SLS_Session object
	 * @see SLS_Generic::getObjectHttpRequest
	 * @see SLS_Generic::getObjectCookie
	 * @see SLS_Generic::getObjectLang
	 * @see SLS_Generic::getObjectMemberSession
	 * @see SLS_Generic::getObjectSecurity
	 * @since 1.0
	 */
	public function getObjectSession() 
	{
		return $this->_session;
	}	
	
	/**
	 * Get the http object	 
	 *
	 * @access public
	 * @return SLS_HttpRequest $http the SLS_HttpRequest object
	 * @see SLS_Generic::getObjectSession
	 * @see SLS_Generic::getObjectCookie
	 * @see SLS_Generic::getObjectLang
	 * @see SLS_Generic::getObjectMemberSession
	 * @see SLS_Generic::getObjectSecurity
	 * @since 1.0
	 */
	public function getObjectHttpRequest() 
	{
		return $this->_httpRequest;
	}
	
	/**
	 * Get the cache object	 
	 *
	 * @access public
	 * @return SLS_Cache $cache the SLS_Cache object
	 * @since 1.0.9
	 */
	public function getObjectCache() 
	{
		return $this->_cache;
	}
	
	/**
	 * Get the cookie object	 
	 *
	 * @access public
	 * @return SLS_Cookie $cookie the SLS_Cookie object
	 * @see SLS_Generic::getObjectSession
	 * @see SLS_Generic::getObjectHttpRequest
	 * @see SLS_Generic::getObjectLang
	 * @see SLS_Generic::getObjectMemberSession
	 * @see SLS_Generic::getObjectSecurity
	 * @since 1.0
	 */	
	public function getObjectCookie($name)
	{
		return $this->_cookie = new SLS_Cookie($name);
	}
	
	/**
	 * Get the lang object	 
	 *
	 * @access public
	 * @return SLS_Lang $lang the SLS_Lang object
	 * @see SLS_Generic::getObjectSession
	 * @see SLS_Generic::getObjectHttpRequest
	 * @see SLS_Generic::getObjectCookie
	 * @see SLS_Generic::getObjectMemberSession
	 * @see SLS_Generic::getObjectSecurity
	 * @since 1.0
	 */	
	public function getObjectLang() 
	{
		return $this->_lang;
	}
	
	/**
	 * Get the membersession object	 
	 *
	 * @access public
	 * @return SLS_MemberSession $mSession the SLS_MemberSession object
	 * @see SLS_Generic::getObjectSession
	 * @see SLS_Generic::getObjectHttpRequest
	 * @see SLS_Generic::getObjectCookie
	 * @see SLS_Generic::getObjectLang
	 * @see SLS_Generic::getObjectSecurity
	 * @since 1.0
	 */	
	public function getObjectMemberSession() 
	{
		return $this->_memberSession;
	}
	
	/**
	 * Get the security object	 
	 *
	 * @access public
	 * @return SLS_Security $security the SLS_Security object
	 * @see SLS_Generic::getObjectSession
	 * @see SLS_Generic::getObjectHttpRequest
	 * @see SLS_Generic::getObjectCookie
	 * @see SLS_Generic::getObjectLang
	 * @see SLS_Generic::getObjectMemberSession
	 * @since 1.0
	 */	
	public function getObjectSecurity() 
	{
		if (!is_object($this->_security))		
			$this->_security = SLS_Security::getInstance();		
		return $this->_security;
	} 
	
	/**
	 * Get the SLS_XMLToolbox object of the site configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite site configs object
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getSiteXML() 
	{
		return $this->_configSite;
	}
	
	/**
	 * Get the SLS_XMLToolbox object of the cache configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite cache configs object
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0.9
	 */
	public function getCacheXML() 
	{
		return $this->_configCache;
	}
	
	/**
	 * Get the SLS_XMLToolbox object of the mail configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite mail configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getMailXML() 
	{
		return $this->_mails;
	}
	
	/**
	 * Get the SLS_XMLToolbox object of the database configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite database configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getDbXML() 
	{
		return $this->_configDb;
	}
	
	/**
	 * Get the SLS_XMLToolbox object of the paths configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite paths configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getPathsXML() 
	{
		return $this->_paths;
	}
	
	/**
	 * Get the SLS_XMLToolbox object of the project configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite project configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getProjectXML() 
	{
		if (is_object($this->_project))
			return $this->_project;
		else 
			SLS_Tracing::addTrace(new Exception("You are not in the good context to get some Project settings"));
	}
	
	/**
	 * Get the SLS_XMLToolbox object of the controllers configs	 
	 *
	 * @access public
	 * @return SLS_XMLToolbox $configSite controllers configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getPluginXml
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getControllersXML() 
	{
		return $this->_controllers;
	}
	
	/**
	 * Return SLS_XMLToolbox instance of the Plugin xml Wanted
	 *
	 * @access public
	 * @param string $xml like "id_pluginname"
	 * @return SLS_XMLToolbox $configPlugin plugins configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getCoreXml
	 * @since 1.0
	 */
	public function getPluginXml($xml)
	{		
		if (is_file($this->getPathConfig("configPlugins").$xml.".xml"))
			return new SLS_XMLToolbox(file_get_contents($this->getPathConfig("configPlugins").$xml.".xml"));
		else 
			SLS_Tracing::addTrace(new Exception("XML Plugin '".$xml."' doesn't exist"));
	}
	
	/**
	 * Return SLS_XMLToolbox instance of the Core xml Wanted
	 *
	 * @access public
	 * @param string $xml
	 * @return SLS_XMLToolbox config core core configs object
	 * @see SLS_Generic::getSiteXML
	 * @see SLS_Generic::getMailXML
	 * @see SLS_Generic::getDbXML
	 * @see SLS_Generic::getPathsXML
	 * @see SLS_Generic::getProjectXML
	 * @see SLS_Generic::getControllersXML
	 * @see SLS_Generic::getPluginXml
	 * @since 1.0
	 */
	public function getCoreXml($xml)
	{
		return $this->_slsCoreXml[$xml];
	}
	
	/**
	 * Load Project Settings. Called only if the side is user
	 *
	 * @access public
	 * @since 1.0
	 */
	public function loadProjectSettings()
	{
		// Open file configs of Project
		$projectHandle = file_get_contents($this->_root.$this->_configSecurePath."/project.xml");
		$this->_project = new SLS_XMLToolbox($projectHandle);	
	}
		
	/**
	 * Get SLS param
	 *
	 * @access public
	 * @param string $config
	 * @param string $xml array key of xml wanted
	 * @return $param
	 * @see SLS_Generic::getSiteConfig
	 * @see SLS_Generic::getMailConfig
	 * @see SLS_Generic::getDbConfig
	 * @see SLS_Generic::getPathConfig
	 * @see SLS_Generic::getProjectConfig
	 * @since 1.0
	 */
	public function getCoreConfig($config, $xml)
	{
		(!is_array($this->_dataCache['SLS'][$xml])) ? $this->_dataCache['SLS'][$xml] = array() : "";
		if (array_key_exists($config, $this->_dataCache['SLS'][$xml]))
			return $this->_dataCache['SLS'][$xml][$config];
		
		else 
		{
			$this->_dataCache['SLS'][$xml][$config] = array_shift($this->_slsCoreXml[$xml]->getTags("//sls_configs/".$config));
			return $this->_dataCache['SLS'][$xml][$config];
		}
	}
	
	/**
	 * Get site config
	 *
	 * @access public
	 * @param string $config
	 * @param string $domainAlias the alias domain
	 * @return string $param
	 * @see SLS_Generic::getCoreConfig
	 * @see SLS_Generic::getMailConfig
	 * @see SLS_Generic::getDbConfig
	 * @see SLS_Generic::getPathConfig
	 * @see SLS_Generic::getProjectConfig
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getSiteConfig("domainName"));
	 * // will produce "doc.sillysmart.org"
	 */
	public function getSiteConfig($config,$domainAlias="")
	{
		$domains = 	$this->_configSite->getTags("//configs/domainName/domain");
		$domainAliasResult = array_shift($this->_configSite->getTags("//configs/domainName/domain[@alias='".$domainAlias."']"));
		if ($config == "domainName" && !empty($domainAlias) && !empty($domainAliasResult))				
			return $domainAliasResult;
		
		if (!array_key_exists($config, $this->_dataCache['Site']))
		{
			// Hack for Principal DomainName
			if ($config == "domainName")
			{		
				if (in_array($_SERVER['HTTP_HOST'], $domains))
					$this->_dataCache['Site'][$config] = $_SERVER['HTTP_HOST'];
				else if (in_array($_SERVER['HTTP_X_FORWARDED_HOST'], $domains))
					$this->_dataCache['Site'][$config] = $_SERVER['HTTP_X_FORWARDED_HOST'];
				else
					$this->_dataCache['Site'][$config] = array_shift($this->_configSite->getTags("//configs/domainName/domain[@default='1']"));				
			}
			else
				$this->_dataCache['Site'][$config] = array_shift($this->_configSite->getTags("//configs/".$config));

		}
		return $this->_dataCache['Site'][$config];
	}
	
	/**
	 * Check if the current app has a cdn defined
	 * 
	 * @access public
	 * @return bool $hasCdn true if cdn, else false
	 * @see SLS_Generic::getCdn
	 * @since 1.0.9
	 */
	public function hasCdn()
	{
		$cdn = $this->_configSite->getTag("//configs/domainName/domain[@cdn='true']");
		return (!empty($cdn)) ? true : false;
	}
	
	/**
	 * Get the current app cdn
	 * 
	 * @access public
	 * @return string $cdnAlias the cdn alias
	 * @see SLS_Generic::hasCdn
	 * @since 1.0.9 
	 */
	public function getCdn()
	{
		$cdn = $this->_configSite->getTag("//configs/domainName/domain[@cdn='true']/@alias");
		return (!empty($cdn)) ? $cdn : "";
	}

	/**
	 * Get mail config
	 *
	 * @access public
	 * @param string $config
	 * @return string $param
	 * @see SLS_Generic::getCoreConfig
	 * @see SLS_Generic::getSiteConfig
	 * @see SLS_Generic::getDbConfig
	 * @see SLS_Generic::getPathConfig
	 * @see SLS_Generic::getProjectConfig
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getMailConfig("defaultReturn"));
	 * // will produce "return"
	 */
	public function getMailConfig($config)
	{
		if (array_key_exists($config, $this->_dataCache['Mail']))
			return $this->_dataCache['Mail'][$config];
		else 
		{
			$this->_dataCache['Mail'][$config] = $this->_mails->getTag("//mails/".$config);
			return $this->_dataCache['Mail'][$config];
		}
	}
	
	/**
	 * Get database config
	 *
	 * @access public
	 * @param string $config the key wanted
	 * @param string $db the alias db wanted (empty will choose the default database)
	 * @return string $param
	 * @see SLS_Generic::getCoreConfig
	 * @see SLS_Generic::getSiteConfig
	 * @see SLS_Generic::getMailConfig
	 * @see SLS_Generic::getPathConfig
	 * @see SLS_Generic::getProjectConfig
	 * @since 1.0
	 */
	public function getDbConfig($config,$db="")
	{
		if (empty($db))
		{
			$result = array_shift($this->_configDb->getTagsAttribute("//dbs/db[@isDefault='true']","alias"));
			$db = $result["attribute"];
		}		
		if (is_array($this->_dataCache['Dbs'][$db]) && array_key_exists($config, $this->_dataCache['Dbs'][$db]))
			return $this->_dataCache['Dbs'][$db][$config];
		else 
		{
			$this->_dataCache['Dbs'][$db][$config] = SLS_Security::getInstance()->decrypt($this->_configDb->getTag("//dbs/db[@alias='".$db."']/".$config), $this->getSiteConfig("privateKey"));
			return $this->_dataCache['Dbs'][$db][$config];
		}
	}
	
	/**
	 * Get path configs
	 *
	 * @access public
	 * @param string $path
	 * @return string $param
	 * @see SLS_Generic::getCoreConfig
	 * @see SLS_Generic::getSiteConfig
	 * @see SLS_Generic::getMailConfig
	 * @see SLS_Generic::getDbConfig
	 * @see SLS_Generic::getProjectConfig
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getPathConfig("controllers"));
	 * // will produce "Mvc/Controllers/"
	 */
	public function getPathConfig($path) 
	{
		if (array_key_exists($path, $this->_dataCache['Paths']))
			return $this->_dataCache['Paths'][$path];
		else 
		{
			$this->_dataCache['Paths'][$path] = $this->_root.($this->_paths->getTag("//paths/".$path));
			return $this->_dataCache['Paths'][$path];
		}
	}
	
	/**
	 * Get project configs
	 *
	 * @access public
	 * @param string $path
	 * @return string $path
	 * @see SLS_Generic::getCoreConfig
	 * @see SLS_Generic::getSiteConfig
	 * @see SLS_Generic::getMailConfig
	 * @see SLS_Generic::getDbConfig
	 * @see SLS_Generic::getPathConfig
	 * @since 1.0
	 */
	public function getProjectConfig($path) 
	{
		if (array_key_exists($path, $this->_dataCache['Project']))
			return $this->_dataCache['Project'][$path];
		else 
		{
			$this->_dataCache['Project'][$path] = $this->_project->getTag("//project".((!empty($path)) ? "/" : "").$path);
			return $this->_dataCache['Project'][$path];
		}
	}
	
	/**
	 * Get Global Protocol
	 *
	 * @access public
	 * @return string $protocol http or https
	 * @see SLS_Generic::getControllerProtocol
	 * @see SLS_Generic::getActionProtocol
	 * @see SLS_Generic::setProtocol	 
	 * @since 1.0
	 */
	public function getProtocol()
	{
		if ($this->_protocol == '')
			$this->_protocol = $this->getSiteConfig("protocol");
		return $this->_protocol;
	}
	
	/**
	 * Get Protocol for a controller
	 *
	 * @access public
	 * @param string $controllerID the id of the controller
	 * @return string $protocol http or https
	 * @see SLS_Generic::getProtocol
	 * @see SLS_Generic::getActionProtocol
	 * @see SLS_Generic::setProtocol	 
	 * @since 1.0
	 */
	public function getControllerProtocol($controllerID="")
	{
		if (empty($controllerID))
			$controllerID = $this->getControllerId();
		$protocol = array_shift($this->_controllers->getTags("//controllers/controller[@id='".$controllerID."']/@protocol"));
		if (empty($protocol))
			return $this->getSiteConfig('protocol');
		else 
			return $protocol;
	}
	
	/**
	 * Get Protocol for an action
	 *
	 * @access public
	 * @param string $actionID the id of the action
	 * @return string $protocol http or https
	 * @see SLS_Generic::getProtocol
	 * @see SLS_Generic::getControllerProtocol
	 * @see SLS_Generic::setProtocol
	 * @since 1.0
	 */
	public function getActionProtocol($actionID="")
	{
		if (empty($actionID))
			$actionID = $this->getActionId();
		$protocol = array_shift($this->_controllers->getTags("//controllers/controller/scontrollers/scontroller[@id='".$actionID."']/@protocol"));
		if (empty($protocol))
		{
			$controllerID = array_shift($this->_controllers->getTags("//controllers/controller[scontrollers/scontroller[@id='".$actionID."']]/@id"));
			$protocol = $this->getControllerProtocol($controllerID);
			if (empty($protocol))
				return $this->_generic->getSiteConfig('protocol');
			else 
				return $protocol;
		}
		else 
			return $protocol;
	}
	
	/**
	 * Set Protocol
	 *
	 * @access public
	 * @param string $protocol http or https
	 * @return bool $set true if ok, else false
	 * @see SLS_Generic::getProtocol
	 * @see SLS_Generic::getControllerProtocol
	 * @see SLS_Generic::getActionProtocol
	 * @since 1.0
	 */
	public function setProtocol($protocol)
	{
		if ($protocol != 'http' && $protocol != 'https')
			return false;
		$this->_protocol = $protocol;
		return true;
	}
	
	/**
	 * Destroy cache variables
	 *
	 * @access public
	 * @param string $type 'Mail' or 'Site' or 'Dbs' or 'Paths'
	 * @param string $var
	 * @since 1.0
	 */
	public function eraseCache($type=null, $var=null)
	{
		if ($type === null)
		{
			$this->_dataCache['Mail'] = array();
			$this->_dataCache['Site'] = array();
			$this->_dataCache['Dbs'] = array();
			$this->_dataCache['Paths'] = array();
			$this->_dataCache['Objects']['user'] = array();
			$this->_dataCache['Objects']['core'] = array();
			return;
		}
		else 
		{
			if (!array_key_exists($type, $this->_dataCache))
				SLS_Tracing::addTrace(new Exception("Key given to erase DataCache is incorrect. Must be 'Mail' | 'Site' | 'Dbs' | 'Paths'"));
			else 
			{
				if ($var === null)
					$this->_dataCache[$type] = array();
				else 
				{
					if (!array_key_exists($var, $this->_dataCache[$type]))
						SLS_Tracing::addTrace(new Exception("The value to erase DataCache in ".$type." is incorrect."));
					else 
						unset($this->_dataCache[$type][$var]);
				}
				return;
			}
		}
	}
	
	/**
	 * Returns an array of all registered links
	 *
	 * @access public
	 * @return array $links array of links
	 * @since 1.0
	 */
	public function getRegisteredLinks()
	{
		return $this->_linksRegistred;
	}
	
	/**
	 * Set the generic name of the current controller	 
	 *
	 * @access public
	 * @param string $name
	 * @see SLS_Generic::setGenericScontrollerName
	 * @see SLS_Generic::getGenericControllerName
	 * @see SLS_Generic::getGenericScontrollerName
	 * @since 1.0
	 */
	public function setGenericControllerName($name) 
	{
		$this->_genericController = $name;
		if (!empty($name))
		{
			$arr = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$name."']/@side"));
			$protocol = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$name."']/@protocol"));			
			$this->setSide($arr);
			$this->setControllerId(array_shift($this->_controllers->getTags("//controllers/controller[@name='".$name."']/@id")));
			if (!empty($protocol) && $this->getProtocol() != $protocol)
				$this->setProtocol($protocol);
		}		
	}
	
	/**
	 * Set the generic name of the current scontroller
	 *
	 * @access public
	 * @param string $name
	 * @see SLS_Generic::setGenericControllerName
	 * @see SLS_Generic::getGenericControllerName
	 * @see SLS_Generic::getGenericScontrollerName
	 * @since 1.0
	 */
	public function setGenericScontrollerName($name)
	{
		$this->_genericScontroller = $name;
	}
	
	/**
	 * Get the generic name of the current controller
	 *
	 * @access public
	 * @return string
	 * @see SLS_Generic::setGenericControllerName
	 * @see SLS_Generic::setGenericScontrollerName
	 * @see SLS_Generic::getGenericScontrollerName
	 * @since 1.0
	 */
	public function getGenericControllerName() 
	{
		return $this->_genericController;
	}
	
	/**
	 * Get the generic name of the current scontroller
	 *
	 * @access public
	 * @return string
	 * @see SLS_Generic::setGenericControllerName
	 * @see SLS_Generic::setGenericScontrollerName
	 * @see SLS_Generic::getGenericControllerName
	 * @since 1.0
	 */
	public function getGenericScontrollerName()
	{
		return $this->_genericScontroller;
	}
	
	/**
	 * Set Controller ID
	 *
	 * @access public
	 * @param string $id the controller id
	 * @param SLS_Generic::setActionId
	 * @since 1.0
	 */
	public function setControllerId($id)
	{
		$this->_controllerId = $id;
	}
	
	/**
	 * Set Action ID
	 *
	 * @access public
	 * @param string $id the action id
	 * @param SLS_Generic::setControllerId
	 * @since 1.0
	 */
	public function setActionId($id)
	{
		$protocol = array_shift($this->_controllers->getTags("//controllers/controller/scontrollers/scontroller[@id='".$id."']/@protocol"));
		if (!empty($protocol) && $this->getProtocol() != $protocol)
			$this->setProtocol($protocol);
		$this->_actionId = $id;
	}
	
	/**
	 * Get the application Side
	 *
	 * @access public
	 * @return string $side 'user' or 'sls'
	 * @see SLS_Generic::setSide
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getSide());
	 * // will produce "user" or "sls"
	 */
	public function getSide()
	{
		return $this->_side;
	}
	
	/**
	 * Set the Application Side
	 *
	 * @access public
	 * @param SLS_String $side 'user' or 'sls'
	 * @see SLS_Generic::getSide
	 * @since 1.0
	 */
	public function setSide($side)
	{
		($side != "user" && $side != "sls") ? SLS_Tracing::addTrace(new Exception("Error, you've tried to set the side with an incorrect value. Side Values should be 'user' or 'sls'")) : $this->_side = $side;
		$this->getObjectSession()->setParam('current_side', $side);
		if ($side == "user")
		{
			$this->includeUserObjects(); 
			$this->loadProjectSettings();
		}
	}

	/**
	 * Get the Application Cache Array
	 *
	 * @access public
	 * @return array
	 * @since 1.0
	 */
	public function getCache()
	{
		return $this->_dataCache;
	}
	
	/**
	 * Redirect on the site using domain + queryString	 
	 *
	 * @access public
	 * @param string $queryString the query string to redirect
	 * @param string $domainAlias the alias domain
	 * @see SLS_Generic::goDirectTo
	 * @see SLS_Generic::dispatch
	 * @see SLS_Generic::getFullPath
	 * @see SLS_Generic::getFullUrl
	 * @see SLS_Generic::getTranslatedController
	 * @see SLS_Generic::redirectOnPreviousPage
	 * @since 1.0
	 * @example 
	 * $this->_generic->redirect("http://www.google.fr");
	 */
	public function redirect($queryString,$domainAlias="")
	{
		if (SLS_String::startsWith($queryString,"http://") || SLS_String::startsWith($queryString, "https://"))
			header("Location: ".$queryString);
		else
			header("Location: ".$this->getProtocol()."://".$this->getSiteConfig("domainName",$domainAlias)."/".$queryString);
		die();
	}
	
	/**
	 * Funtion Merging SLS_Generic::redirect() and SLS_Generic::getFullUrl()
	 *
	 * @access public
	 * @param string $controller
	 * @param string $scontroller
	 * @param array $more
	 * @param string $lang
	 * @param string $domainAlias the alias domain
	 * @since 1.0.1
	 */
	public function forward($controller,$scontroller,$more=array(),$lang=false,$domainAlias="")
	{
		if (!empty($more) && is_array($more) && !is_array($more[0]))
		{
			$realMore = array();
			foreach($more as $column => $value)
				$realMore[] = array("key" => $column, "value" => $value);
			$more = $realMore;
		}
		$url = $this->getFullPath($controller,$scontroller,$more,true,$lang,$domainAlias);
		$this->redirect($url);
	}
	
	/**
	 * Funtion Merging SLS_Generic::redirect() and SLS_Generic::getFullUrl()
	 *
	 * @access public
	 * @param string $controller
	 * @param string $scontroller
	 * @param array $more
	 * @param string $lang
	 * @param string $domainAlias the alias domain
	 * @see SLS_Generic::redirect
	 * @see SLS_Generic::dispatch
	 * @see SLS_Generic::getFullPath
	 * @see SLS_Generic::getFullUrl
	 * @see SLS_Generic::getTranslatedController
	 * @see SLS_Generic::redirectOnPreviousPage
	 * @since 1.0
	 * @example 
	 * $this->_generic->goDirectTo("Home","Index");
	 */
	public function goDirectTo($controller,$scontroller,$more=array(),$lang=false,$domainAlias="")
	{
		if (!empty($more) && is_array($more) && !is_array($more[0]))
		{
			$realMore = array();
			foreach($more as $column => $value)
				$realMore[] = array("key" => $column, "value" => $value);
			$more = $realMore;
		}
		
		$url = $this->getFullPath($controller,$scontroller,$more,true,$lang,$domainAlias);
		$this->redirect($url);
	}
	
	/**
	 * Dispatch the controller and the action without reloading the application	 
	 *
	 * @access public
	 * @param string $controller generic controller name
	 * @param string $action generic scontroller name	 
	 * @param array $http set new params for $_GET and $_POST
	 * <code>
	 * array(
	 * 		"POST" 	=> array("key" => "value"), 
	 * 		"GET" 	=> array("key" => "value")
	 * )
	 * </code>	 
	 * @see SLS_Generic::redirect
	 * @see SLS_Generic::goDirectTo
	 * @see SLS_Generic::getFullPath
	 * @see SLS_Generic::getFullUrl
	 * @see SLS_Generic::getTranslatedController
	 * @see SLS_Generic::redirectOnPreviousPage
	 * @since 1.0
	 * @example 
	 * $this->_generic->dispatch("Home","Index");
	 */
	public function dispatch($controller,$action,$http=array("POST"=>array(),"GET"=>array()))
	{
		SLS_FrontController::getInstance($this)->dispatch($controller,$action,$http);
	}
	
	/**
	 * Get a full url	 
	 *
	 * @access public
	 * @param string $controller the controller name
	 * @param string $scontroller the scontroller name
	 * @param array $more key|value optionnal params
	 * @param bool $finishUrl true if u want to append the defaultExtension, else false
	 * @param string $lang the lang you want
	 * @param string $domainAlias the alias domain
	 * @param bool $withDomain if you want to prefix url by domain (default true)
	 * @return string $url the full url
	 * @see SLS_Generic::redirect
	 * @see SLS_Generic::goDirectTo
	 * @see SLS_Generic::dispatch
	 * @see SLS_Generic::getFullUrl
	 * @see SLS_Generic::getTranslatedController
	 * @see SLS_Generic::redirectOnPreviousPage
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getFullPath("Home","Index"));
	 * // will produce : "http://doc.sillysmart.org/Home/Welcome.sls"
	 */
	public function getFullPath($controller,$scontroller,$more=array(),$finishUrl=true,$lang=false,$domainAlias="",$withDomain=true)
	{	
		$urlArray = $this->getTranslatedController($controller,$scontroller,$lang);
		$url = (($withDomain) ? $urlArray['protocol']."://".$this->getSiteConfig("domainName",$domainAlias) : "")."/";
		if (!empty($more) && is_array($more) && !is_array($more[0]))
		{
			$realMore = array();
			foreach($more as $column => $value)
				$realMore[] = array("key" => $column, "value" => $value);
			$more = $realMore;
		}
		
		$url .= ($this->urlRewriteEnabled()) ? ($urlArray["controller"]."/".$urlArray["scontroller"]) : ("index.php?mode=".$urlArray["controller"]."&amp;smode=".$urlArray["scontroller"]); 
		
		for($i=0 ; $i<$count=count($more) ; $i++)
			$url .= ($this->urlRewriteEnabled()) ? ("/".$more[$i]["key"]."/".$more[$i]["value"]) : ("&amp;".$more[$i]["key"]."=".$more[$i]["value"]);
		$defaultExtension = $this->getSiteConfig('defaultExtension');
		if ($finishUrl && !empty($defaultExtension))
			$url .= ".".$defaultExtension;
		
		return $url;
	}
	
	/**
	 * Get a full url	 
	 *
	 * @access public
	 * @param string $controller the controller name
	 * @param string $scontroller the scontroller name
	 * @param array $more key|value optionnal params
	 * @param bool $finishUrl true if u want to append the defaultExtension, else false
	 * @param string $lang the lang you want
	 * @param string $domainAlias the alias domain
	 * @return string $url the full url
	 * @see SLS_Generic::redirect
	 * @see SLS_Generic::goDirectTo
	 * @see SLS_Generic::dispatch
	 * @see SLS_Generic::getFullPath
	 * @see SLS_Generic::getTranslatedController
	 * @see SLS_Generic::redirectOnPreviousPage
	 * @since 1.0 
	 */
	public function getFullUrl($controller,$scontroller,$more=array(),$finishUrl=true,$lang=false,$domainAlias="")
	{
		return $this->getFullPath($controller,$scontroller,$more,$finishUrl,$lang,$domainAlias);
	}
	
	/**
	 * Return the controller and the scontroller translated into the wanted language
	 *
	 * @access public
	 * @param string $controller
	 * @param string $scontroller
	 * @param string{2}[optionnal] $lang
	 * @return $array $controller <code>array('controller'] => '..', 'scontroller' => '..', 'protocol' => '..')</code>
	 * @see SLS_Generic::redirect
	 * @see SLS_Generic::goDirectTo
	 * @see SLS_Generic::dispatch
	 * @see SLS_Generic::getFullPath
	 * @see SLS_Generic::getFullUrl
	 * @see SLS_Generic::redirectOnPreviousPage
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getTranslatedController("Home","Index"));
	 * // will produce :
	 * array(
  	 * 		"protocol"		=> "http",
  	 * 		"controller"	=> "Home",
  	 * 		"scontroller"	=> "Welcome"
	 * )
	 */
	public function getTranslatedController($controller, $scontroller, $lang=false)
	{	
		if ($lang === false)		
			$lang = $this->getObjectLang()->getLang();
					
		$this->_translatedControllerName[$lang][$controller] = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$controller."']/controllerLangs/controllerLang[@lang='".$lang."']"));
		$this->_translatedScontrollerName[$lang][$scontroller] = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$scontroller."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']"));
		$controllerP = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$controller."']/@protocol"));
		$scontrollerP = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$scontroller."']/@protocol"));		
		if (!empty($scontrollerP))
			$return['protocol'] = $scontrollerP;
		else if (!empty($controllerP))
			$return['protocol'] = $controllerP;
		else 
			$return['protocol'] = $this->getSiteConfig("protocol");
		$return['controller'] = $this->_translatedControllerName[$lang][$controller];
		$return['scontroller'] = $this->_translatedScontrollerName[$lang][$scontroller];
			
		return $return;
	}
	
	/**
	 * Redirect the user on the previous page if exists, else on the home page
	 *
	 * @access public
	 * @see SLS_Generic::redirect
	 * @see SLS_Generic::goDirectTo
	 * @see SLS_Generic::dispatch
	 * @see SLS_Generic::getFullPath
	 * @see SLS_Generic::getFullUrl
	 * @see SLS_Generic::getTranslatedController
	 * @since 1.0
	 */
	public function redirectOnPreviousPage()
	{
		$mode = $this->getObjectSession()->getParam("previousMode");
		$smode = $this->getObjectSession()->getParam("previousSmode");
		$more = $this->getObjectSession()->getParam("previousMore");
		
		// If we have in session a previous page
		if (!empty($mode) && !empty($smode))
		{			
			$actualLang = $this->getObjectLang()->getLang();
									
			$controller = array_shift($this->_controllers->getTags("//controllers/controller[controllerLangs[controllerLang[@lang='".$actualLang."']='".$mode."']]/@name"));
			$scontroller = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[scontrollerLangs/scontrollerLang[@lang='".$actualLang."']='".$smode."']/@name"));
			if (empty($controller) || empty($scontroller))
				$this->redirect("");
			$urlArray = $this->getTranslatedController($controller,$scontroller);
					
			$this->redirect(($this->urlRewriteEnabled()) ? ($urlArray["controller"]."/".$urlArray["scontroller"].$more) : ($this->getProtocol()."://".$this->getSiteConfig("domainName",$domainAlias)."/"."index.php?mode=".$urlArray["controller"]."&smode=".$urlArray["scontroller"].$more));				
		}
		// Else, redirect on home
		else					
			$this->redirect("");		
	}
	
	/**
	 * Get actions list without specifics params of one controller	 
	 *
	 * @access public
	 * @param string $controller the controller to check
	 * @return array array of actions
	 * @since 1.0
	 */
	public function getActionsNoParams($controller)
	{		
		return $this->_controllers->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@needParam='0']/@name");		
	}	
	
	/**
	 * Get the buffer XML
	 * 
	 * @access public
	 * @return string XML
	 * @see SLS_Generic::setBufferXML
	 * @since 1.0
	 */
	public function getBufferXML()
	{
		return $this->_bufferXML;
	}
	
	/**
	 * Set the buffer XML
	 *  
	 * @access public
	 * @param $xml str the xml string
	 * @param $incremental bool
	 * @param string $tag
	 * @see SLS_Generic::getBufferXML
	 * @since 1.0
	 */
	public function setBufferXML($xml, $incremental=true, $tag=NULL)
	{
		if ($incremental === true && $tag === NULL)
			SLS_Tracing::addTrace(new Exception("A tag missed if you want to set XML with an incremental Option in SLS_Generic::setBufferXML()"));
		if ($incremental === true && $tag !== NULL)
		{
			$xmlToolBox = new SLS_XmlToolBox($this->_bufferXML);
			$xmlToolBox->appendXMLNode($tag, $xml);
			$this->_bufferXML = $xmlToolBox->getXML();
		}
		else	
			$this->_bufferXML = $xml;
	}
	
	/**
	 * Check if the site is in production
	 *
	 * @access public
	 * @return bool $prod true if yes, else false
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->isProd());
	 * // will produce : true or false
	 */
	public function isProd()
	{		
		if (is_bool($this->_isProd))
			return $this->_isProd;
		
		else 
		{			
			$isProd = $this->getSiteConfig("isProd");
			
			if ($isProd == 0) 
			{				
				$this->_isProd = false;
				return false;
			}			 	
			if ($isProd == 1)
			{				
				$this->_isProd = true;
				return true;
			}
		}
	}
	
	/**
	 * Check if the cache is enabled
	 *
	 * @access public
	 * @return bool $cache true if yes, else false
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->isCache());
	 * // will produce : true or false
	 */
	public function isCache()
	{
		if (is_bool($this->_isCache))
			return $this->_isCache;
			
		else 
		{
			$isProd = $this->getSiteConfig("activeCache");
			if ($isProd === 0)
			{
				$this->_isCache = false;
				return false;
			}			 	
			if ($isProd === 1)
			{
				$this->_isCache = true;
				return true ;
			}				
		}
	}
	
	/**
	 * Check if the site is in maintenance
	 *
	 * @access public
	 * @return bool $maintenance true if yes, else false
	 * @since 1.0.7
	 * @example 
	 * var_dump($this->_generic->isMaintenance());
	 * // will produce : true or false
	 */
	public function isMaintenance()
	{		
		if (is_bool($this->_isMaintenance))
			return $this->_isMaintenance;
		
		else 
		{			
			$isMaintenance = $this->getSiteConfig("activeMaintenance");
			
			if ($isMaintenance == 0) 
			{				
				$this->_isMaintenance = false;
				return false;
			}			 	
			if ($isMaintenance == 1)
			{				
				$this->_isMaintenance = true;
				return true;
			}
		}
	}
	
	/**
	 * Check if the site has monitoring logs enabled
	 *
	 * @access public
	 * @return bool $monitoring true if yes, else false
	 * @since 1.0.9
	 * @example 
	 * var_dump($this->_generic->isMonitoring());
	 * // will produce : true or false
	 */
	public function isMonitoring()
	{		
		if (is_bool($this->_isMonitoring))
			return $this->_isMonitoring;
		
		else 
		{			
			$isMonitoring = $this->getSiteConfig("activeMonitoring");
			
			if ($isMonitoring == 0) 
			{				
				$this->_isMonitoring = false;
				return false;
			}			 	
			if ($isMonitoring == 1)
			{				
				$this->_isMonitoring = true;
				return true;
			}
		}
	}
	
	/**
	 * Check if we are on the customer bo
	 * 
	 * @access public
	 * @return bool true if yes, else false
	 * @since 1.0.9
	 */
	public function isBo()
	{
		return ($this->getControllersXML()->getTag("//controllers/controller[@isBo='true']/@id") == $this->getControllerId()) ? true : false;
	}
	
	/**
	 * Get the generic controller name of user bo
	 * 
	 * @access public
	 * @return string $bo the controller name of the user bo
	 * @since 1.1
	 */
	public function getBo()
	{
		return $this->getControllersXML()->getTag("//controllers/controller[@isBo='true']/@name");
	}
	
	/**
	 * Register a link into the XML
	 *
	 * @access public
	 * @param string $codeName codeName of the link: permit to retrieve it with xsl
	 * @param string $controller name of generic controller if to the same domain or entire url if it's not
	 * @param string $scontroller needed if on the same domain
	 * @param array $args arguments
	 * <code>
	 * array('key1' => 'value1', 'key2' => 'value2', .., 'keyN' => '..')
	 * </code>
	 * You can set null to set no arguments or false to have an incomplete url like http://www.example.com/Controller/Action
	 * @param bool $projectDomain true if the link is on the Project domain
	 * @param bool $https If on the same domain, choose your protocol, true for https, false for http
	 * @return bool
	 * @since 1.0
	 */
	public function registerLink($codeName, $controller, $scontroller=null, $args=null, $projectDomain=true, $https=false)
	{
		if ($projectDomain && (empty($controller) || is_null($scontroller)))
		{
			SLS_Tracing::addTrace(new Exception("Warning, you missed Controller or SController to register a link on the same domain"));
			return false;
		}
		if (!$projectDomain && empty($controller))
		{
			SLS_Tracing::addTrace(new Exception("Warning, you missed the URL to register a link on a different domain"));
			return false;
		}
		if (is_null($title))
			$title = $name;
		if (empty($codeName))
		{
			SLS_Tracing::addTrace(new Exception("Warning, you missed the code name for to register a link"));
			return false;
		}
		$link['codeName'] = $codeName;
		if ($projectDomain)
		{
			$controllers = $this->getTranslatedController($controller, $scontroller);
			$arg = "";
			if (empty($controllers['controller']) && empty($controllers['scontroller']))
			{
				SLS_Tracing::addTrace(new Exception("Warning, Controller and Scontroller given to register a link on the same domain have not been found"));
				return false;
			}
			if (is_array($args))
			{
				
				$arg = "/";
				foreach($args as $key=>$value)
					$arg .= $key."/".$value."/";
				$arg = substr($arg, 0, (strlen($arg)-1));
				
			}
			$link['href'] = ($controllers['protocol'] == "http") ? "http://" : "https://";
			$defaultExt = $this->getSiteConfig("defaultExtension");
			if ($args === false)
				unset($defaultExt);
				
			$link['href'] .= (!empty($defaultExt)) ? $this->getSiteConfig("domainName")."/".$controllers['controller']."/".$controllers['scontroller'].$arg.".".$defaultExt : $this->getSiteConfig("domainName")."/".$controllers['controller']."/".$controllers['scontroller'].$arg;
			
		}
		else 
		{
			((substr($controller, 0, 7) != "http://") && (substr($controller, 0, 8) != "https://")) ? $controller = "http://".$contoller : "";
			$link['href'] = $controller;
			
		}
		array_push($this->_linksRegistred, $link);
		return true;
	}
	
	/**
	 * Permit to delete a path recursively
	 *
	 * @access public
	 * @param string $filepath the path from which delete can start
	 * @return bool $delete true if deleted, else false
	 * @since 1.0
	 */
	public function rm_recursive($filepath)
	{
	    if (is_dir($filepath) && !is_link($filepath))
	    {
	        if ($dh = opendir($filepath))
	        {
	            while (($sf = readdir($dh)) !== false)
	            {
	                if ($sf == '.' || $sf == '..')
	                {
	                    continue;
	                }
	                if (!$this->rm_recursive($filepath.'/'.$sf))
	                {
	                   SLS_Tracing::addTrace(new Exception($filepath.'/'.$sf.' could not be deleted.'));
	                }
	            }
	            closedir($dh);
	        }
	        return rmdir($filepath);
	    }
	    return unlink($filepath);
	}
	
	/**
	 * Switch the current application language and reload the current page
	 *
	 * @access public
	 * @param string $lang the lang to switch
	 * @param string $controller the generic controller to reroute after changing lang (if empty, previous controller)
	 * @param string $scontroller the generic scontroller to reroute after changing lang (if empty, previous action)
	 * @param string $domainAlias the alias domain
	 * @since 1.0
	 * @example 
	 * $this->_generic->switchLang("fr");
	 * // will change your current language to 'french'
	 */
	public function switchLang($lang,$controller="",$scontroller="",$params="",$domainAlias="")
	{
		$mode = $this->getObjectSession()->getParam("previousMode");
		$smode = $this->getObjectSession()->getParam("previousSmode");
		$more = $this->getObjectSession()->getParam("previousMore");
		if (!empty($params))
		{
			$more = "";
			foreach($params as $paramKey => $paramValue)
				$more .= "/".$paramKey."/".$paramValue;
		}
		$controller = (empty($controller)) ? $this->getObjectSession()->getParam("previousController") : $controller;
		$scontroller = (empty($scontroller)) ? $this->getObjectSession()->getParam("previousScontroller") : $scontroller;
		$generic = SLS_Generic::getInstance();
		$domainAlias = 	$generic->getSiteXML()->getTag("//configs/domainName/domain[@lang = '".$lang."']/@alias");
		$actualLang = $this->getObjectLang()->getLang();
		
		// If unknown lang, redirect on current page without switching lang
		if (!in_array($lang,$this->getObjectLang()->getSiteLangs()))			
			$this->redirect($mode."/".$smode.$more,$domainAlias);
		
		// If we don't have generic controllers but translated controllers, try to recover generics
		if ((empty($controller) && empty($scontroller)) && (!empty($mode) && !empty($smode)))
		{
			$controller = array_shift($this->_controllers->getTags("//controllers/controller[controllerLangs[controllerLang[@lang='".$actualLang."']='".$mode."']]/@name"));
			$scontroller = array_shift($this->_controllers->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[scontrollerLangs/scontrollerLang[@lang='".$actualLang."']='".$smode."']/@name"));
		}
		
		// If impossible to relocate previous controllers, redirect on home in the new lang
		if (empty($controller) || empty($scontroller))
		{
			$this->getObjectLang()->setLang($lang);
			
			$this->forward("Home","Index",array(),$lang,(!empty($domainAlias)) ? $domainAlias : "");
		}
		
		// If we are here, it's cool :)
		else
		{
			$this->getObjectLang()->setLang($lang);
			$urlArray = $this->getTranslatedController($controller,$scontroller,$lang);
			$this->redirect($this->getSiteConfig("protocol")."://".$this->getSiteConfig("domainName",(!empty($domainAlias)) ? $domainAlias : "")."/".$urlArray["controller"]."/".$urlArray["scontroller"].$more);
		}
	}
	
	/**
	 * Generate a Controller UniqID
	 *
	 * @access public
	 * @return string $uniqID id
	 * @see SLS_Generic::generateActionId
	 * @see SLS_Generic::getControllerId
	 * @see SLS_Generic::getActionId
	 * @see SLS_Generic::translateActionId
	 * @see SLS_Generic::actionIdExists
	 * @since 1.0
	 */
	public function generateControllerId()
	{		
		$uniqID = uniqid("c_");
		while(count($this->_controllers->getTags("//controllers/controller[@id='".$uniqID."']")) > 0)
			$uniqID = uniqid("c_");
			
		return $uniqID;
	}
	
	/**
	 * Generate an Action UniqID
	 *
	 * @access public
	 * @return string $uniqID id
	 * @see SLS_Generic::generateControllerId
	 * @see SLS_Generic::getControllerId
	 * @see SLS_Generic::getActionId
	 * @see SLS_Generic::translateActionId
	 * @see SLS_Generic::actionIdExists
	 * @since 1.0
	 */
	public function generateActionId()
	{		
		$uniqID = uniqid("a_");
		while(count($this->_controllers->getTags("//controllers/controller/scontrollers/scontroller[@id='".$uniqID."']")) > 0)
			$uniqID = uniqid("a_");
			
		return $uniqID;
	}
	
	/**
	 * Return current Controller ID
	 *
	 * @access public
	 * @param string $controller generic controller
	 * @return string $id the current controller id or a specific controller id
	 * @see SLS_Generic::generateControllerId
	 * @see SLS_Generic::generateActionId
	 * @see SLS_Generic::getActionId
	 * @see SLS_Generic::translateActionId
	 * @see SLS_Generic::actionIdExists
	 * @since 1.0
	 */
	public function getControllerId($controller="")
	{
		if (empty($controller))		
			return $this->_controllerId;
		else
		{						
			$result = array_shift($this->_controllers->getTagsAttributes("//controllers/controller[@name='".$controller."']",array("id")));			
			return $result["attributes"][0]["value"];
		}
	}
	
	/**
	 * Check if the given controllerId exists
	 * 
	 * @access public
	 * @param string $controllerId the controller id to check
	 * @return bool true if exists, else false
	 * @see SLS_Generic::generateControllerId
	 * @see SLS_Generic::generateActionId
	 * @see SLS_Generic::getControllerId
	 * @see SLS_Generic::translateActionId
	 * @since 1.0.9
	 */
	public function controllerIdExists($controllerId)
	{		
		$result = array_shift($this->_controllers->getTags("//controllers/controller[@id='".$controllerId."']"));
		return (!empty($result)) ? true : false;
	}
	
	/**
	 * Return Controller ID of a Action ID
	 *
	 * @access public
	 * @param string $controller generic controller
	 * @return string $id the current action id or a specific action id
	 * @see SLS_Generic::getControllerId
	 * @see SLS_Generic::getActionId
	 * @since 1.0.9
	 */
	public function getControllerIdFromActionId($action_id="")
	{
		if (empty($action_id))		
			return $this->_controllerId;
		else
			return $this->_controllers->getTag("//controllers/controller[scontrollers/scontroller[@id='".$action_id."']]/@id");
	}
	
	/**
	 * Return Current Action ID
	 *
	 * @access public
	 * @param string $controller generic controller
	 * @param string $action generic action
	 * @return string $id the current action id or a specific action id
	 * @see SLS_Generic::generateControllerId
	 * @see SLS_Generic::generateActionId
	 * @see SLS_Generic::getControllerId
	 * @see SLS_Generic::translateActionId
	 * @see SLS_Generic::actionIdExists
	 * @since 1.0
	 */
	public function getActionId($controller="",$action="")
	{
		if (empty($controller) || empty($action))
			return $this->_actionId;
		else
		{						
			$result = array_shift($this->_controllers->getTagsAttributes("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."']",array("id")));			
			return $result["attributes"][0]["value"];
		}
	}
	
	/**
	 * 
	 * Get action id translation
	 * 
	 * @access public
	 * @param string $actionId the action id to transalte
	 * @param string $lang the wanted language (current language if empty)
	 * @return mixed controller/action of the given action id, false if unknown action id
	 * @see SLS_Generic::generateControllerId
	 * @see SLS_Generic::generateActionId
	 * @see SLS_Generic::getControllerId	 
	 * @see SLS_Generic::actionIdExists
	 * @since 1.0.6
	 */
	public function translateActionId($actionId,$lang="")
	{		
		if (!$this->actionIdExists($actionId))
			return false;
		
		if (empty($lang))
			$lang = $this->getObjectLang()->getLang();
		
		$controller = array_shift($this->_controllers->getTags("//controllers/controller[scontrollers/scontroller[@id='".$actionId."']]/controllerLangs/controllerLang[@lang='".$lang."']"));
		$scontroller = array_shift($this->_controllers->getTags("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']"));
		
		return (!empty($controller) && !empty($scontroller)) ? array("controller" => $controller, "scontroller" => $scontroller) : false;
	}
	
	/**
	 * Check if the given actionId exists
	 * 
	 * @access public
	 * @param string $actionId the action id to check
	 * @return bool true if exists, else false
	 * @see SLS_Generic::generateControllerId
	 * @see SLS_Generic::generateActionId
	 * @see SLS_Generic::getControllerId
	 * @see SLS_Generic::translateActionId
	 * @since 1.0.6
	 */
	public function actionIdExists($actionId)
	{		
		$result = array_shift($this->_controllers->getTags("//controllers/controller/scontrollers/scontroller[@id='".$actionId."']"));
		return (!empty($result)) ? true : false;
	}
	
	/**
	 * Get the current template
	 *
	 * @access public
	 * @param string $tpl the template name
	 * @see SLS_Generic::setCurrentTpl
	 * @since 1.0.1
	 * @example 
	 * var_dump($this->_generic->getCurrentTpl());
	 * // will produce "__default"
	 */
	public function getCurrentTpl()
	{
		return $this->_currentTpl;
	}
	
	/**
	 * Set the current template you want to use with your action
	 *
	 * @access public
	 * @param string $tpl the template name
	 * @return bool $set true if ok, else false
	 * @see SLS_Generic::getCurrentTpl
	 * @since 1.0.1
	 */
	public function setCurrentTpl($tpl)
	{
		$templates = array();
		
		$handle = opendir($this->getPathConfig(($this->_side == "user") ? "viewsTemplates" : "coreViewsTemplates"));
		while($file = readdir($handle))
		{
			if (is_file($this->getPathConfig(($this->_side == "user") ? "viewsTemplates" : "coreViewsTemplates").$file) && substr($file, 0, 1) != ".")
			{
				$fileName 	= SLS_String::substrBeforeLastDelimiter($file,".");
				$extension 	= SLS_String::substrAfterLastDelimiter($file,".");
				
				if ($extension == "xsl")
					array_push($templates,$fileName);
			}
		}
		closedir($handle);
		
		if (in_array($tpl,$templates))
		{
			$this->_currentTpl = $tpl;
			return true;
		}
		else
		{			
			SLS_Tracing::addTrace(new Exception("Warning: you choose an unknown template ('".$tpl."')"));
			return false;
		}
	}
	
	/**
	 * Get all view custom vars
	 * 
	 * @access public
	 * @return array $view_custom_vars all keys/values
	 * @since 1.0.9
	 */
	public function getViewCustomVars()
	{
		return $this->_view_custom_vars;
	}
	
	/**
	 * Get a view custom var
	 * 
	 * @access public
	 * @param string $key the wanted key
	 * @return mixed $view_custom_var the wanted value
	 * @since 1.0.9
	 */
	public function getViewCustomVar($key)
	{
		if ($this->existViewCustomVar($key))
		{
			return $this->_view_custom_vars[$key];
		}
		else
			return false;		
	}
	
	/**
	 * Add/Erase a view custom var
	 * 
	 * @access public
	 * @param string $key the wanted key
	 * @param string $value the wanted value
	 * @return bool true if set, else false
	 * @since 1.0.9
	 */
	public function addViewCustomVar($key,$value)
	{
		if (!empty($key) && !empty($value))
		{
			$this->_view_custom_vars[$key] = $value;
			return true;
		}
		else
			return false;
	}
	
	/**
	 * Delete a view custom var
	 * 
	 * @access public
	 * @param string $key the wanted key to delete
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public function delViewCustomVar($key)
	{
		if ($this->existViewCustomVar($key))
		{
			unset($this->_view_custom_vars[$key]);
			return true;
		}
		else
			return false;
	}
	
	/**
	 * Check if a view custom var exists
	 * 
	 * @access public
	 * @param string $key the key to check
	 * @param bool true if exists, else false
	 * @since 1.0.9
	 */
	public function existViewCustomVar($key)
	{
		return (!empty($key)) ? array_key_exists($key,$this->_view_custom_vars) : false;
	}
	
	/**
	 * Copy of SLS_XmlToolBox()	 
	 *
	 * @access private
	 * @param string $flow
	 * @param string $tag
	 * @return array $values
	 * @since 1.0
	 */
	private function getXmlTags($flow, $tag)
	{
		$values = array();
	
		$dom = new DOMDocument();		 
		$dom->loadXML($flow);
		$xpath = new DOMXPath($dom);
		
		$results = $xpath->query($tag);

		for ($i=0 ; $i<$results->length ; $i++)
			array_push($values,(string)$results->item($i)->nodeValue);
		
		return $values;
	}
	
	/**
	 * Check if it's a valid XML
	 *
	 * @access public
	 * @param string $xml the XML
	 * @return bool true if valid, else false
	 * @since 1.0.6
	 */
	public function isValidXML($xml)
	{
		try
		{
			$doc = new DOMDocument();
		    @$doc->loadXML($xml);
		
		    $errors = libxml_get_errors();
		    
		    return (empty($errors)) ? true : false;
		}
		catch (Exception $e)
		{
			return false;
		}
	}
	
	/**
	 * Monitor execution time
	 *	
	 * @access public
	 * @param float $start
	 * @since 1.0.5
	 */
	public function monitor($start="")
	{
		return round((microtime(true)- ((empty($start)) ? $this->_time_start : $start)), 4);
	}
	
	/**
	 * Log execution time
	 * 
	 * @access public
	 * @param float $time execution time
	 * @param string $message message to log
	 * @param string $detail detail
	 * @param string $type type of process
	 * @since 1.0.5
	 */
	public function logTime($time,$message,$detail="",$type="unknown")
	{
		#echo "[".$type."] || ".date("Y-m-d H:i:s")." || ".$time." || ".$message." || "."Detail: ".$detail."<br />";
		
		// Developer monitoring
		if ($this->getSide() == "user" && $this->getBo() != $this->getGenericControllerName())
		{
			$types = array("statics" 		=> array("time" => "0", "logs" => array()),
						   "components" 	=> array("time" => "0", "logs" => array()),
						   "routing" 		=> array("time" => "0", "msg"  => ""),
						   "init" 			=> array("time" => "0", "msg"  => ""),
						   "action" 		=> array("time" => "0", "msg"  => ""),
						   "sql" 			=> array("time" => "0", "logs" => array()),
						   "parsing_html" 	=> array("time" => "0", "msg"  => ""),
						   "parsing_xsl" 	=> array("time" => "0", "msg"  => ""),
						   "flush_cache" 	=> array("time" => "0", "logs" => array()));
			$logSession = $this->_session->getParam("sls_dev_logs");
			$devLogs = (empty($logSession)) ? $types : $logSession;
			switch($type)
	    	{
	    		case "Controller Static":
	    			$devLogs["statics"]["time"] += $time;
	    			$devLogs["statics"]["logs"][] = array("time" => $time, "msg" => trim(SLS_String::substrAfterFirstDelimiter($detail,"Controller:")));
	    			break;
	    		case "Controller Component":
	    			$devLogs["components"]["time"] += $time;
	    			$devLogs["components"]["logs"][] = array("time" => $time, "msg" => trim(SLS_String::substrAfterFirstDelimiter($detail,"Controller:")));
	    			break;
	    		case "Controller Front":
	    			$mapping = trim(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($message,"Resolve Mapping ("),")"));
	    			$mappingController = SLS_String::substrBeforeFirstDelimiter($mapping,"/");
	    			$mappingAction = SLS_String::substrAfterFirstDelimiter($mapping,"/");
	    			$devLogs["routing"]["time"] += $time;
	    			$devLogs["routing"]["msg"] = SLS_String::printArray(array("mode" => $mappingController, "smode" => $mappingAction));
	    			break;
	    		case "Controller Init":
	    			$devLogs["init"]["time"] += $time;
	    			break;
	    		case "Controller Action":
	    			$devLogs["action"]["time"] += $time;
	    			break;
	    		case "MySQL Query":
					$message = trim((!SLS_String::startsWith($detail,"Query:")) ? $message : SLS_String::substrAfterFirstDelimiter($detail,"Query:"));
					if ($message == "MySQL Connecting")
						$message = "Connection"." |n|".$detail;
					$message = str_replace(array("|n|"),array("\n"),$message);
	    			$devLogs["sql"]["time"] += $time;
	    			$devLogs["sql"]["logs"][] = array("time" => $time, "msg" => $message);
	    			break;
	    		case "HTML Parsing":
	    			$devLogs["parsing_html"]["time"] += $time;
	    			break;
	    		case "XML/XSL Parsing":
	    			$devLogs["parsing_xsl"]["time"] += $time;
	    			break;
	    		case "Flush Cache":
	    			$devLogs["flush_cache"]["time"] += $time;
	    			$devLogs["flush_cache"]["logs"][] = array("time" => $time, "msg" => $detail);
	    			break;
	    	}
	    	$this->_session->setParam("sls_dev_logs",$devLogs);
		}
		
		if (!$this->isMonitoring())
			return;
		
		// Objects
		$nbOccurencesFiles = 0;
		$nbMaxLines = 5000;
		$directory = "monitoring/".date("Y-m");
		$fileName = date("Y-m-d");
		$filePath = "";
		
		// Check if monitoring directory exists
		if (!file_exists($this->getPathConfig("logs")."monitoring"))
			mkdir($this->getPathConfig("logs")."monitoring",0777);	
		
		// If month directory doesn't exists, create it			
		if (!file_exists($this->getPathConfig("logs").$directory))			
			mkdir($this->getPathConfig("logs").$directory,0777);
			
		// Count the number of hits of log file			
		$handle = opendir($this->getPathConfig("logs").$directory);
		while (false !== ($file = readdir($handle)))
			if (SLS_String::startsWith($file,$fileName))
				$nbOccurencesFiles++;
    	closedir($handle);
    	
    	// If the current file log doesn't exists, create it		    
	    if ($nbOccurencesFiles == 0)
	    {
	    	touch($this->getPathConfig("logs").$directory."/".$fileName."_0.log");
	    	$filePath = $this->getPathConfig("logs").$directory."/".$fileName."_0.log";
	    }
	    // Else, locate it
	    else	    
	    	$filePath = $this->getPathConfig("logs").$directory."/".$fileName."_".($nbOccurencesFiles-1).".log";
	    
	    // Then, if and only if the file log has been located, increased or created : write into the logs		    
	    if (is_file($filePath) && $this->getSide() == "user")
	    {
	    	$oldContentLog = file_get_contents($filePath);
	    	$newContentLog = "".$type." || ".date("Y-m-d H:i:s")." || ".$time." || ".$message." || ".$detail;
	    	if (SLS_String::endsWith($newContentLog,"\n"))
	    		$newContentLog = SLS_String::substrBeforeLastDelimiter($newContentLog,"\n");
	    	$newContentLog = str_replace("\n","|n|",$newContentLog);
	    	    	
	    	file_put_contents($filePath,$newContentLog."\n".$oldContentLog,LOCK_EX);
	    		    	
	    	if ($type == "Render")
	    	{
	    		$oldContentLog = file_get_contents($filePath);
	    		$newContentLog = "#|end|#";
	    		file_put_contents($filePath,$newContentLog."\n".$oldContentLog,LOCK_EX);
	    		
		    	// If the max number of lines has been reach, increase the file log version		    
			    if (SLS_String::countLines(file_get_contents($filePath)) >= $nbMaxLines)
			    {
			    	touch($this->getPathConfig("logs").$directory."/".$fileName."_".$nbOccurencesFiles.".log");
			    	$filePath = $this->getPathConfig("logs").$directory."/".$fileName."_".$nbOccurencesFiles.".log";
			    }
	    	}
	    }
	}
	
	/**
	 * Flush logs (monitoring & application) older than 2 months
	 * 
	 * @access private
	 * @since 1.0.8
	 */
	private function flushLogs()
	{
		$date = SLS_String::substrBeforeLastDelimiter(SLS_Date::timestampToDate(strtotime("-3 month")),"-");
		if (!file_exists($this->getPathConfig("logs")."monitoring"))
			@mkdir($this->getPathConfig("logs")."monitoring");
		$handle = opendir($this->getPathConfig("logs")."monitoring");
		
		// Foreach directories, if it's older than 2 months, delete it
		while (false !== ($dir = readdir($handle)))		
			if (is_dir($this->getPathConfig("logs")."monitoring/".$dir) && substr($dir, 0, 1) != "." && ($dir <= $date))
				$this->rm_recursive($this->getPathConfig("logs")."/monitoring/".$dir);
				
		$handle = opendir($this->getPathConfig("logs"));
		
		// Foreach directories, if it's older than 2 months, delete it
		while (false !== ($dir = readdir($handle)))		
			if (is_dir($this->getPathConfig("logs").$dir) && substr($dir, 0, 1) != "." && $dir != "monitoring" && ($dir <= $date))
				$this->rm_recursive($this->getPathConfig("logs").$dir);
	}
	
	/**
	 * Check if mod_rewrite is enabled
	 * 
	 * @access public
	 * @return bool true if yes, else false
	 * @since 1.0.8
	 */
	public function urlRewriteEnabled() 
	{
		$realScriptName = $_SERVER['SCRIPT_NAME'];
		$virtualScriptName = reset(explode("?", $_SERVER['REQUEST_URI']));
		return !($realScriptName==$virtualScriptName);
	} 
}
?>