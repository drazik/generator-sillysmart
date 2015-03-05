<?php
/**
 * Tool SLS_HttpRequest - Http Request Treatment
 *  
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Url
 * @since 1.0  
 */
class SLS_HttpRequest 
{
	private $_params;
	private $_paramsGet = array();
	private $_paramsPost = array();
	private $_paramsFiles = array();
					
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct()
	{
		// Merge all params
		$this->_params = array_merge_recursive($_POST,$_GET,$this->fixFilesArray($_FILES));
		// Strip extension if exists		
		if (SLS_String::endsWith($this->_params['smode'], SLS_Generic::getInstance()->getSiteConfig('defaultExtension')))		
            $this->_params['smode'] = SLS_String::substrBeforeLastDelimiter($this->_params['smode'], '.'.SLS_Generic::getInstance()->getSiteConfig('defaultExtension'));
        // Command line
      	if (PHP_SAPI === 'cli')
      	{
      		global $argv;
      		if (is_array($argv))
      		{	
      			$args = array_slice($argv, 1);
      			$controllerPosition = 0;
      			for($i=0 ; $i<count($args) ; $i++)
      			{
      				$argKey = SLS_String::substrBeforeFirstDelimiter($args[$i],"=");
      				if (!empty($argKey) && strtolower($argKey) == "mode")
      				{
      					$this->_params['mode'] = SLS_String::substrAfterFirstDelimiter($args[$i],"=");
      					$controllerPosition = $i;
      				}
      				if (!empty($argKey) && strtolower($argKey) == "smode")
      				{
      					$args[$i] = SLS_String::substrAfterFirstDelimiter($args[$i],"=");
      				}
      			}
      			unset($args[$controllerPosition]);
      			$this->_params['smode'] = str_replace("=","/",implode("/",$args));
      		}
      	}
        // Get smode
		$explode = explode("/", $this->_params['smode']);
		$this->_params['smode'] = array_shift($explode);		
		// Transform url in classic queryString '?param1=value1&param2=value2...'
		$queryString = "";
		$params = array_chunk($explode, 2);		
		for($i=0 ; $i<$count=count($params) ; $i++)		
			if (count($params[$i]) == 2)
				$queryString .= (($i == 0) ? '' : '&').$params[$i][0].'='.(($params[$i][1] != "|sls_empty|") ? $params[$i][1] : "");		
		// Get all params/values
		parse_str($queryString,$params);		
		if (!empty($params))
		{
			foreach($params as $key => $value)
				$this->_params[$key] = $value;
		}
	}
	
	/**
	 * Return a value
	 *
	 * @access public
	 * @param string $key the parameter key
	 * @param string $type the type of the key requested. should be POST, GET, FILE
	 * @return string $param the parameter
	 * @since 1.0
	 */
	public function getParam($key, $type=null)
	{
		if ($type == null)	
		{	
			if(isset($this->_params[$key]))
				return $this->_params[$key];
		}
		else 
		{
			$type = strtoupper($type);
			
			if ($type != 'POST' && $type != 'GET' && $type != 'FILE')
				SLS_Tracing::addTrace(new Exception("The parameter 'type' should be 'POST', 'GET' or 'FILE'. Current value = ".var_dump($type)));
			else 
			{
				switch($type)
				{
					case 'POST':
						return $_POST[$key];
						break;
					case 'GET':
						$params = $_GET;
			
						// Strip extension if exists		
						if (SLS_String::endsWith($params['smode'], SLS_Generic::getInstance()->getSiteConfig('defaultExtension')))		
							$params['smode'] = SLS_String::substrBeforeLastDelimiter($params['smode'], '.'.SLS_Generic::getInstance()->getSiteConfig('defaultExtension'));
						// Get smode        
						$explode = explode("/", $params['smode']);
						$params['smode'] = array_shift($explode);		
						// Transform url in classic queryString '?param1=value1&param2=value2...'
						$queryString = "";
						$params = array_chunk($explode, 2);		
						for($i=0 ; $i<$count=count($params) ; $i++)		
							if (count($params[$i]) == 2)
								$queryString .= (($i == 0) ? '' : '&').$params[$i][0].'='.(($params[$i][1] != "|sls_empty|") ? $params[$i][1] : "");		
						// Get all params/values
						parse_str($queryString,$params);		
						if (!empty($params))
						{
							foreach($params as $keyP => $valueP)
								$params[$keyP] = $valueP;
						}				
						return $params[$key];
						break;
					case 'FILE':
						return $_FILE[$key];
						break;
				}
			}
		}		
	}
	
	/**
	 * Return all parameters (GET, POST or FILES)
	 *
	 * @access public	 
	 * @param string $type the type you want ('ALL','POST','GET','FILES')
	 * @return array $params array of all paramters
	 * @since 1.0
	 * @example 
	 * var_dump($this->_http->getParams());
	 * // will produce :
	 * array(
  	 * 		"mode"		=> "Home",
  	 * 		"smode"		=> "Welcome",
  	 * 		"..."		=> "..."
	 * )
	 */
	public function getParams($type='all') 
	{
		$type = strtoupper($type);
		if ($type != 'ALL' && $type != 'POST' && $type != 'GET' && $type != 'FILES')
			SLS_Tracing::addTrace(new Exception("To use the method SLS_HttpRequest::getParams(), you need to specify a correct type of value ('all', 'post', 'get' or 'files')"));
		else
		{
			if ($type == 'ALL') 
				return $this->_params;
			elseif ($type == 'POST')			
				return $_POST;
			elseif ($type == 'GET')
			{
				$params = $_GET;
			
				// Strip extension if exists		
				if (SLS_String::endsWith($params['smode'], SLS_Generic::getInstance()->getSiteConfig('defaultExtension')))		
					$params['smode'] = SLS_String::substrBeforeLastDelimiter($params['smode'], '.'.SLS_Generic::getInstance()->getSiteConfig('defaultExtension'));
				// Get smode        
				$explode = explode("/", $params['smode']);
				$params['smode'] = array_shift($explode);		
				// Transform url in classic queryString '?param1=value1&param2=value2...'
				$queryString = "";
				$params = array_chunk($explode, 2);		
				for($i=0 ; $i<$count=count($params) ; $i++)		
					if (count($params[$i]) == 2)
						$queryString .= (($i == 0) ? '' : '&').$params[$i][0].'='.(($params[$i][1] != "|sls_empty|") ? $params[$i][1] : "");		
				// Get all params/values
				parse_str($queryString,$params);		
				if (!empty($params))
				{
					foreach($params as $key => $value)
						$params[$key] = $value;
				}				
				return $params;
			}			
			elseif ($type == 'FILES')
				return $_FILES;
			else 
				return $this->_params;
		}
	}
	
	/**
	 * Fix $_FILES native PHP array
	 * 
	 * @access public
	 * @param array $files
	 * @return array $ret
	 * @since 1.0.9
	 */
	public function fixFilesArray($files)
	{
	    $ret = array();
	    if(isset($files['tmp_name']))
	    {
	        if (is_array($files['tmp_name']))
	        {
	            foreach($files['tmp_name'] as $id => $tmpName)
	            {
	                $ret[$id] = array('name' 		=> $files['name'][$id], 
	                				  'tmp_name' 	=> $tmpName, 
	                				  'size' 		=> $files['size'][$id], 
	                				  'type' 		=> $files['type'][$id], 
	                				  'error' 		=> $files['error'][$id]);
	            }
	        }
	        else
	        {
	            $ret = $files;
	        }
	    }
	    else
	    {
	        foreach ($files as $key => $value)
	        {
	            $ret[$key] = $this->fixFilesArray($value);
	        }
	    }
	    return $ret;
	}
}
?>