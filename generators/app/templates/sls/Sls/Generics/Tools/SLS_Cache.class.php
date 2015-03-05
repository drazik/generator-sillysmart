<?php
/**
 * Tool SLS_Cache - Cache handling
 *  
 * @author Laurent Bientz
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0.9
 */ 
class SLS_Cache
{
	private $_generic;
	private $_binds;
	private $_objects;
	private $_mobile = null;
	
	/**
	 * Constructor, instanciate generic class & get bind
	 * 
	 * @access public
	 * @since 1.0.9
	 */
	public function __construct()
	{
		$this->_generic = SLS_Generic::getInstance();		
		$this->fetchBind();
		$this->fetchObject();
	}
	
	###########################
	# OBJECTS/TABLES BINDINGS #
	###########################
	
	/**
	 * Fetch models binding with static|component|controller|action from file controller_bind.json
	 * 
	 * @access public
	 * @since 1.0.9
	 */
	public function fetchBind()
	{
		if (!file_exists($this->_generic->getPathConfig("configSls")."controller_bind.json"))
		{
			try {
				@file_put_contents($this->_generic->getPathConfig("configSls")."controller_bind.json",json_encode(array()));
			}
			catch (Exception $e) {
				SLS_Tracing::addTrace($e);
			}
		}		
		$this->_binds = json_decode(@file_get_contents($this->_generic->getPathConfig("configSls")."controller_bind.json"),true);
	}
	
	/**
	 * Save binds
	 * 
	 * @access public
	 * @return bool true if saved, else false
	 * @since 1.0.9
	 */
	public function saveBind()
	{
		try {
			return  @file_put_contents($this->_generic->getPathConfig("configSls")."controller_bind.json",json_encode($this->_binds));
		}
		catch (Exception $e) {
			SLS_Tracing::addTrace($e);
		}
	}
	
	/**
	 * Add model/object binding
	 * 
	 * @access public
	 * @param string $table the table to bind
	 * @param string $key the object type (statics|components|controllers|actions) binded with model
	 * @param string $value the object name (static|component) or id (controller|action)
	 * @return bool true if added, else false
	 * @since 1.0.9
	 */
	public function addBind($table,$key,$value)
	{		
		$allowedKeys = array("statics", "components", "controllers", "actions");
		if (!in_array($key,$allowedKeys))
			return false;
		
		if (!array_key_exists($table,$this->_binds))
		{
			$this->_binds[$table] = array("statics" 	=> array(),
										  "components" 	=> array(),
										  "controllers" => array(),
										  "actions" 	=> array());
		}
		
		if (!in_array($value,$this->_binds[$table][$key]))
			$this->_binds[$table][$key][] = $value;	
		
		return true;
	}
	
	/**
	 * Get table binding
	 * 
	 * @access public
	 * @param string $table the table to bind
	 * @param string $key the object type (statics|components|controllers|actions) binded with model
	 * @return mixed array of statics|components|controllers|actions if table found, else false
	 * @since 1.0.9
	 */
	public function getBind($table,$key)
	{
		$allowedKeys = array("statics", "components", "controllers", "actions");
		if (!in_array($key,$allowedKeys))
			return false;
		
		return (array_key_exists($key,$this->_binds[$table])) ? $this->_binds[$table][$key] : false;
	}
	
	############################
	# /OBJECTS/TABLES BINDINGS #
	############################
	
	####################
	# OBJECTS SETTINGS #
	####################
	
	/**
	 * Fetch objects cache properties (statics|components)
	 * 
	 * @access public
	 * @since 1.0.9
	 */
	public function fetchObject()
	{
		if (!file_exists($this->_generic->getPathConfig("configSls")."objects_cache.json"))
		{
			try {
				@file_put_contents($this->_generic->getPathConfig("configSls")."objects_cache.json",json_encode(array("statics" => array(), "components" => array())));
			}
			catch (Exception $e) {
				SLS_Tracing::addTrace($e);
			}
		}
		$this->_objects = json_decode(@file_get_contents($this->_generic->getPathConfig("configSls")."objects_cache.json"),true);			
	}
	
	/**
	 * Save objects
	 * 
	 * @access public
	 * @return bool true if saved, else false
	 * @since 1.0.9
	 */
	public function saveObject()
	{
		try {
			return @file_put_contents($this->_generic->getPathConfig("configSls")."objects_cache.json",json_encode($this->_objects));
		}
		catch (Exception $e) {
			SLS_Tracing::addTrace($e);
		}
	}
	
	/**
	 * Add object cached properties
	 * 
	 * @access public
	 * @param string $name the object name
	 * @param string $type the object type (statics|components)
	 * @param string $visibility public or private cache
	 * @param string $responsive if the object is reponsive or not
	 * @param int $expire cache expire in seconds
	 * @return bool true if added, else false
	 * @since 1.0.9
	 */
	public function addObject($name,$type,$visibility,$responsive="no_responsive",$expire="0")
	{
		$allowedTypes = array("statics", "components");
		if (!in_array($type,$allowedTypes))
			return false;
		if (!is_numeric($expire) || $expire < 0)
			return false;
				
		if (!array_key_exists($name,$this->_objects[$type]))
			$this->_objects[$type][$name] = array("visibility" 	=> $visibility,
												  "responsive"  => $responsive,
												  "expire" 		=> $expire);
		else
		{
			$this->_objects[$type][$name]["visibility"] = $visibility;
			$this->_objects[$type][$name]["responsive"] = $responsive;
			$this->_objects[$type][$name]["expire"] = $expire;
		}
		return true;
	}
	
	/**
	 * Delete object cached properties
	 * 
	 * @access public
	 * @param string $name the object name
	 * @param string $type the object type (statics|components)
	 * @return bool true if delete, else false
	 * @since 1.1
	 */
	public function deleteObject($name,$type)
	{
		$allowedTypes = array("statics", "components");
		if (!in_array($type,$allowedTypes))
			return false;
			
		if (array_key_exists($name,$this->_objects[$type]))
			unset($this->_objects[$type][$name]);
			
		return true;
	}
	
	/**
	 * Get object cached properties
	 * 
	 * @access public
	 * @param string $name the object name
	 * @param string $type the object type (statics|components)
	 * @param string $key the wanted key (visibility|expire)
	 * @return mixed visibility or expire if found, else false
	 * @since 1.0.9
	 */
	public function getObject($name,$type,$key)
	{
		$allowedTypes = array("statics", "components");
		if (!in_array($type,$allowedTypes))
			return false;
		
		return (array_key_exists($name,$this->_objects[$type]) && array_key_exists($key,$this->_objects[$type][$name])) ? $this->_objects[$type][$name][$key] : false;
	}
	
	/**
	 * Get action cached properties
	 * 
	 * @access public
	 * @param string $action_id the action_id wanted - current if empty
	 * @return mixed array of cache properties if found, else false
	 * @since 1.0.9 
	 */
	public function getAction($action_id="")
	{
		if (empty($action_id))
			$action_id = $this->_generic->getActionId();
			
		if (!$this->_generic->actionIdExists($action_id))
			return false;
			
		$result = $this->_generic->getControllersXML()->getTag("//controllers/controller/scontrollers/scontroller[@id='".$action_id."']/@cache");
		
		return (empty($result)) ? false : explode("|",$result);
	}
	
	#####################
	# /OBJECTS SETTINGS #
	#####################
	
	##################
	# CACHE FEATURES #
	##################
	
	/**
	 * Check if cache directory exists
	 * 
	 * @access public
	 * @return bool true if exists or created, else false
	 * @since 1.0.9
	 */
	public function checkCache()
	{ 
		try {
			return (file_exists($this->_generic->getPathConfig("cache"))) ? true : @mkdir($this->_generic->getPathConfig("cache"));
		}
		catch (Exception $e) {
			SLS_Tracing::addTrace($e);
		}	
	}
	
	/**
	 * Save a cached full page
	 * 
	 * @access public
	 * @param string $html the final html
	 * @param string $visibility public or private cache - private if empty
	 * @param string $responsive if the object is reponsive or not 
	 * @param string $controller the controller id (controllers.xml @id="c_{id}") - current if empty
	 * @param string $action the action id (controllers.xml @id="a_{id}") - current if empty
	 * @param string $queryString the query string url - current if empty
	 * @return bool true if saved, else false
	 * @see SLS_Cache::saveCachePartial
	 * @see SLS_Cache::getCachePartial
	 * @see SLS_Cache::getCacheFull
	 * @since 1.0.9
	 */
	public function saveCacheFull($html,$visibility="private",$responsive="no_responsive",$controllerId="",$actionId="",$queryString="")
	{
		$this->checkCache();
		
		$visibility 	= $this->getCacheParam("visibility",$visibility);
		$responsive 	= $this->getCacheParam("responsive",$responsive);
		$controllerId 	= $this->getCacheParam("controller",$controllerId);
		$actionId 		= $this->getCacheParam("action",$actionId);
		$queryString 	= $this->getCacheParam("uri",$queryString);
		
		if ($responsive == "responsive")
		{
			$this->loadPluginMobile();
			$device = ($this->_mobile->isMobile() ? ($this->_mobile->isTablet() ? 'tablet' : 'phone') : 'desktop');
		}
		
		if (!$this->_generic->controllerIdExists("c_".SLS_String::substrAfterFirstDelimiter($controllerId,"controller_")) || !$this->_generic->actionIdExists("a_".SLS_String::substrAfterFirstDelimiter($actionId,"action_")))
			return false;
		
		$fileName = $this->_generic->getPathConfig("cache").$controllerId."/".$actionId."/".(($visibility == "private") ? SLS_String::stringToUrl($this->_generic->getObjectSession()->session_id(),"_")."/" : "").(($responsive == "responsive") ? $device : "").$queryString.".html";
		
		self::createDir($fileName);
		
		try {
			return @file_put_contents($fileName,$html);
		}	
		catch (Exception $e) {
			SLS_Tracing::addTrace($e);
		}
	}
	
	/**
	 * Get a cached full page
	 * 
	 * @access public
	 * @param int $expire cache expire in seconds
	 * @param string $visibility public or private cache - private if empty
	 * @param string $responsive if the object is reponsive or not	 
	 * @param string $controller the controller id (controllers.xml @id="c_{id}") - current if empty
	 * @param string $action the action id (controllers.xml @id="a_{id}") - current if empty
	 * @param string $queryString the query string url - current if empty
	 * @return mixed html cached file if success, else false
	 * @see SLS_Cache::saveCachePartial
	 * @see SLS_Cache::getCachePartial
	 * @see SLS_Cache::saveCacheFull 
	 * @since 1.0.9
	 */
	public function getCacheFull($expire,$visibility="private",$responsive="no_responsive",$controllerId="",$actionId="",$queryString="")
	{
		$visibility 	= $this->getCacheParam("visibility",$visibility);
		$responsive 	= $this->getCacheParam("responsive",$responsive);
		$controllerId 	= $this->getCacheParam("controller",$controllerId);
		$actionId 		= $this->getCacheParam("action",$actionId);
		$queryString 	= $this->getCacheParam("uri",$queryString);
						
		if (!$this->_generic->controllerIdExists("c_".SLS_String::substrAfterFirstDelimiter($controllerId,"controller_")) || !$this->_generic->actionIdExists("a_".SLS_String::substrAfterFirstDelimiter($actionId,"action_")))
			return false;
		
		if ($responsive == "responsive")
		{
			$this->loadPluginMobile();
			$device = ($this->_mobile->isMobile() ? ($this->_mobile->isTablet() ? 'tablet' : 'phone') : 'desktop');
		}
		
		$fileName = $this->_generic->getPathConfig("cache").$controllerId."/".$actionId."/".(($visibility == "private") ? SLS_String::stringToUrl($this->_generic->getObjectSession()->session_id(),"_")."/" : "").(($responsive == "responsive") ? $device : "").$queryString.".html";		
		if (file_exists($fileName))
		{
			try {
				if ($expire != 0 && ((filectime($fileName) + $expire) < time()))
				{
					@unlink($fileName);
					return false;
				}			
				else
					return @file_get_contents($fileName);
			}
			catch (Exception $e) {
				SLS_Tracing::addTrace($e);
			}
		}
		else
			return false;
	}
	
	/**
	 * Save a cached partial page
	 * 
	 * @access public
	 * @param string $xml the xml of the object (static|component|action)	 
	 * @param string $type object type (static|component|action) - action if empty
	 * @param string $name the name object or action id (controllers.xml @id="a_{id}") - current if empty
	 * @param string $visibility public or private cache - private if empty
	 * @param string $responsive if the object is reponsive or not
	 * @param int $expire cache expire in seconds
	 * @param string $queryString the query string url - current if empty
	 * @return bool true if saved, else false
	 * @see SLS_Cache::saveCacheFull
	 * @see SLS_Cache::getCachePartial
	 * @see SLS_Cache::getCacheFull
	 * @since 1.0.9
	 */
	public function saveCachePartial($xml,$type="",$name="",$visibility="private",$responsive="no_responsive",$queryString="")
	{
		$this->checkCache();
		
		$type 			= $this->getCacheParam("type",$type);
		$name 			= $this->getCacheParam($type,$name);
		$visibility 	= $this->getCacheParam("visibility",$visibility);
		$responsive 	= $this->getCacheParam("responsive",$responsive);
		$queryString 	= $this->getCacheParam("uri",$queryString);
		$controllerId 	= $this->getCacheParam("controller",$controllerId);
		$actionId 		= $this->getCacheParam("action",$actionId);
		
		if (!$this->objectExists($type,$name))
			return false;
					
		if ($responsive == "responsive")
		{
			$this->loadPluginMobile();
			$device = ($this->_mobile->isMobile() ? ($this->_mobile->isTablet() ? 'tablet' : 'phone') : 'desktop');
		}
			
		if (SLS_String::startsWith($name,"action_"))
			$fileName = $this->_generic->getPathConfig("cache")."controller_".SLS_String::substrAfterFirstDelimiter($this->_generic->getControllerIdFromActionId("a_".SLS_String::substrAfterFirstDelimiter($name,"action_")),"c_")."/".$name."/".(($visibility == "private") ? SLS_String::stringToUrl($this->_generic->getObjectSession()->session_id(),"_")."/" : "").$queryString.".xml";
		else		
			$fileName = $this->_generic->getPathConfig("cache").$name."/".$controllerId."/".$actionId."/".(($visibility == "private") ? SLS_String::stringToUrl($this->_generic->getObjectSession()->session_id(),"_")."/" : "").(($responsive == "responsive") ? $device : "").$queryString.".xml";
				
		self::createDir($fileName);
		
		try {
			return @file_put_contents($fileName,$xml);
		}
		catch (Exception $e) {
			SLS_Tracing::addTrace($e);
		}		
	}
	
	/**
	 * Get a cached partial page
	 * 
	 * @access public	 
	 * @param int $expire cache expire in seconds
	 * @param string $type object type (static|component|action) - action if empty
	 * @param string $name the name object or action id (controllers.xml @id="a_{id}") - current if empty
	 * @param string $visibility public or private cache - private if empty
	 * @param string $responsive if the object is reponsive or not
	 * @return mixed xml cached file if success, else false
	 * @param string $queryString the query string url - current if empty
	 * @see SLS_Cache::saveCachePartial
	 * @see SLS_Cache::saveCacheFull
	 * @see SLS_Cache::getCacheFull
	 * @since 1.0.9
	 */
	public function getCachePartial($expire,$type="",$name="",$visibility="private",$responsive="no_responsive",$queryString="")
	{
		$type 			= $this->getCacheParam("type",$type);
		$name 			= $this->getCacheParam($type,$name);
		$visibility 	= $this->getCacheParam("visibility",$visibility);
		$responsive 	= $this->getCacheParam("responsive",$responsive);
		$queryString 	= $this->getCacheParam("uri",$queryString);
		$controllerId 	= $this->getCacheParam("controller",$controllerId);
		$actionId 		= $this->getCacheParam("action",$actionId);
		
		if (!$this->objectExists($type,$name))
			return false;
		
		if ($responsive == "responsive")
		{
			$this->loadPluginMobile();
			$device = ($this->_mobile->isMobile() ? ($this->_mobile->isTablet() ? 'tablet' : 'phone') : 'desktop');
		}
			
		if (SLS_String::startsWith($name,"action_"))
			$fileName = $this->_generic->getPathConfig("cache")."controller_".SLS_String::substrAfterFirstDelimiter($this->_generic->getControllerIdFromActionId("a_".SLS_String::substrAfterFirstDelimiter($name,"action_")),"c_")."/".$name."/".(($visibility == "private") ? SLS_String::stringToUrl($this->_generic->getObjectSession()->session_id(),"_")."/" : "").(($responsive == "responsive") ? $device : "").$queryString.".xml";
		else
			$fileName = $this->_generic->getPathConfig("cache").$name."/".$controllerId."/".$actionId."/".(($visibility == "private") ? SLS_String::stringToUrl($this->_generic->getObjectSession()->session_id(),"_")."/" : "").(($responsive == "responsive") ? $device : "").$queryString.".xml";
					
		if (file_exists($fileName))
		{
			try {
				if ($expire != 0 && ((filectime($fileName) + $expire) < time()))
				{
					@unlink($fileName);
					return false;
				}			
				else
					return @file_get_contents($fileName);
			}
			catch (Exception $e) {
				SLS_Tracing::addTrace($e);
			}
		}
		else
			return false;
	}
	
	/**
	 * Check if a given object exists (internal method)
	 * 
	 * @access private
	 * @param string $type object type (static|component|action)
	 * @param string $name the name object or action id (controllers.xml @id="a_{id}") prefixed by $type_
	 * @return boold true if existed, else false
	 * @since 1.0.9
	 */
	private function objectExists($type,$name)
	{		
		$name = SLS_String::substrAfterFirstDelimiter($name,"_");
		if ($type == "action")		
			return $this->_generic->actionIdExists("a_".$name);
		else
		{			
			$cacheObject = $this->_generic->getCacheXML();
			if ($type == "static")
				$result = $cacheObject->getTag("//statics/controllers/Site/controller[translate(name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='".$name."controller']");
			else
				$result = $cacheObject->getTag("//statics/components/Site/controller[translate(name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='".$name."controller']");
			return (!empty($result)) ? true : false;
		}		
	}
	
	/**
	 * Clean cache params (internal method)
	 * 
	 * @access private
	 * @param string $key key to check
	 * @param string $value value to check
	 * @return string value cleaned
	 * @since 1.0.9
	 */
	private function getCacheParam($key,$value="")
	{
		switch ($key)
		{
			case "type":
				return (!in_array(strtolower($value),array("static","component","action"))) ? "action" : strtolower($value);
				break;
			case "visibility":
				return (!in_array(strtolower($value),array("private","public"))) ? "private" : strtolower($value);
				break;
			case "responsive":
				return (!in_array(strtolower($value),array("responsive","no_responsive"))) ? "no_responsive" : strtolower($value);
				break;
			case "controller":
				return (empty($value)) ? "controller_".SLS_String::substrAfterFirstDelimiter(strtolower($this->_generic->getControllerId()),"c_") : strtolower($value);
				break;
			case "action":
				return (empty($value)) ? "action_".SLS_String::substrAfterFirstDelimiter(strtolower($this->_generic->getActionId()),"a_") : strtolower($value);
				break;
			case "component":
				return "component_".$value;
				break;
			case "static":
				return "static_".$value;
				break;
			case "uri":
				if (empty($value))
				{
					$bind = $this->_generic->getTranslatedController($this->_generic->getGenericControllerName(),$this->_generic->getGenericScontrollerName());
					$value = "/".$bind["controller"]."/".$bind["scontroller"];
					$params = $this->_generic->getObjectHttpRequest()->getParams();
					uksort($params,"strcasecmp");	
					foreach($params as $key => $val)
					{
						if (!in_array($key,array("mode","smode")))
						{
							if (is_array($val))
							{
								foreach($val as $arr_key => $arr_value)
									$value .= "/".$key."[".$arr_key."]/".$arr_value; 
							}
							else
								$value .= "/".$key."/".$val;
						}
					}
							
					if (empty($value))
						$value = "/";
				}
				
				if (SLS_String::contains($value,"#")) // Remove before anchor
					$value = SLS_String::substrBeforeFirstDelimiter($value,"#");		
				if ($this->_generic->urlRewriteEnabled()) // If rewrite, remove after ?
					$value = SLS_String::substrBeforeFirstDelimiter($value,"?");
					
				$value = SLS_String::stringToUrl($value,"#"); // Remove forbidden chars and join querystring parameters with # glue
				$value = mb_substr($value,0,250,"UTF-8"); // Cut file name at 255 chars for rubbish OS (250 + 5 reserved for extension .xml|.html)
				return $value;
				break;
			default:
				return $value;
		}
	}
	
	/**
	 * Delete cached files from table name
	 * 
	 * @access public
	 * @param string $table the table name from which you want to flush cached files
	 * @since 1.0.9
	 */
	public function flushFromTable($table)
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		if (array_key_exists($table,$this->_binds))
		{			
			foreach($this->_binds[$table]["statics"] as $static)
				$this->flushStatic($static);		
			foreach($this->_binds[$table]["components"] as $component)
				$this->flushComponent($component);	
			foreach($this->_binds[$table]["controllers"] as $controller)
				$this->flushController($controller);		
			foreach($this->_binds[$table]["actions"] as $action)
				$this->flushAction($action);
		}
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Table `".$table."`","Flush Cache");
	}
	
	/**
	 * Flush full cache
	 * 
	 * @access public	 
	 * @since 1.0.9
	 */
	public function flushFull()
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		return self::deleteDir($this->_generic->getPathConfig("cache"));
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Full app cache","Flush Cache");
	}
	
	/**
	 * Flush static's cached files
	 * 
	 * @access public
	 * @param string $static the static name
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public function flushStatic($static)
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		return self::deleteDir($this->_generic->getPathConfig("cache")."static_".strtolower($static));
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Static `".strtolower($static)."`","Flush Cache");
	}
	
	/**
	 * Flush component's cached files
	 * 
	 * @access public
	 * @param string $component the component name
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public function flushComponent($component)
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		return self::deleteDir($this->_generic->getPathConfig("cache")."component_".strtolower($component));
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Component `".strtolower($component)."`","Flush Cache");
	}
	
	/**
	 * Flush controller's cached files
	 * 
	 * @access public
	 * @param string $controller the controller id (controllers.xml @id="c_{id}") - current if empty
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public function flushController($controller="")
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		if (empty($controller))
			$controller = $this->_generic->getControllerId();
		if (SLS_String::startsWith($controller,"c_"))
			$controller = SLS_String::substrAfterFirstDelimiter($controller,"c_");
		return self::deleteDir($this->_generic->getPathConfig("cache")."controller_".strtolower($controller));
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Controller `".strtolower($controller)."`","Flush Cache");
	}
	
	/**
	 * Flush action's cached files
	 * 
	 * @access public
	 * @param string $action the action id (controllers.xml @id="a_{id}") - current if empty
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public function flushAction($action="")
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		if (empty($action))
			$action = $this->_generic->getActionId();
		if (SLS_String::startsWith($action,"a_"))
			$action = SLS_String::substrAfterFirstDelimiter($action,"a_");
		return self::deleteDir($this->_generic->getPathConfig("cache")."controller_".SLS_String::substrAfterFirstDelimiter($this->_generic->getControllerIdFromActionId("a_".$action),"_")."/action_".strtolower($action));
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Action `".strtolower($action)."`","Flush Cache");
	}
	
	/**
	 * Flush user's cached files
	 * 
	 * @access public
	 * @param string $session_id the user's session_id - current session_id if empty
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public function flushUser($session_id="")
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		if (empty($session_id))
			$session_id = $this->_generic->getObjectSession()->session_id();
				
		$files = glob($this->_generic->getPathConfig("cache")."*/".$session_id.".xml");
    	foreach($files as $file)
    		if (is_file($file))
    			@unlink($file);
		$dirs = glob($this->_generic->getPathConfig("cache")."*/*/".$session_id);
		foreach($dirs as $dir)
			self::deleteDir($dir);
			
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Flush Cache","Session_id `".$session_id."`","Flush Cache");
	}
	
	###################
	# /CACHE FEATURES #
	###################
	
	#############
	# UTILITIES #
	#############
	
	/**
	 * Utility to delete a dir recursivly based on path
	 * 
	 * @access public static
	 * @param string $path the path to delete
	 * @return bool true if deleted, else false
	 * @since 1.0.9
	 */
	public static function deleteDir($path)
    {
    	if (!file_exists($path))
    		return false;
    	
    	try {
	        return is_file($path) ?
	                @unlink($path) :
	                array_map(array('SLS_Cache', 'deleteDir'), glob($path.'/{,.svn}*', GLOB_BRACE)) == @rmdir($path);
    	}
		catch (Exception $e) {
			SLS_Tracing::addTrace($e);
		}
    }
    
    /**
     * Create directories recursivly based on path
     * 
     * @access public static
     * @param string $fileName path to file (strip after the last /)
     * @return bool true if created, else false
     * @since 1.0.9
     */
    public static function createDir($fileName)
    {
    	$directories = explode("/",$fileName);		
		array_pop($directories);
    	if (!file_exists(implode("/",$directories)))
    	{
    		try {    	
				@mkdir(implode("/",$directories),0777,true);
    		}
			catch (Exception $e) {
				SLS_Tracing::addTrace($e);
			}
    	}
    }
    
    /**
     * Instanciate SLS_Mobile_Detect
     * 
     * @access public
     * @since 1.1
     */
    public function loadPluginMobile()
    {
    	if (is_null($this->_mobile) && file_exists($this->_generic->getPathConfig("plugins")."SLS_Mobile_Detect.class.php"))
			$this->_mobile = new SLS_Mobile_Detect();
    }
    
    ##############
	# /UTILITIES #
	##############
}