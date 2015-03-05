<?php
/**
 * SLS_Cookie class - Manage cookie
 *  
 * @author Laurent Bientz
 * @copyright SillySmart
 * @package Sls.Generics.Session
 * @since 1.0.5
 */
class SLS_Cookie
{
	private $_name = "";
	private $_value = array();
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param string $name cookie name
	 * @since 1.0.5
	 */
	public function __construct($name)
	{
		$this->_name = $name;
		$value = (!empty($_COOKIE[$this->_name])) ? unserialize(SLS_String::trimSlashesFromString($_COOKIE[$this->_name])) : array();
		$this->_value = ($value === false) ? array("content" => SLS_String::trimSlashesFromString($_COOKIE[$this->_name])) : $value;
	}
	
	/**
	 * Generic getters
	 * 
	 * @access public
	 * @return array $value all keys/values
	 * @since 1.0.5
	 */
	public function getParams()
	{
		return $this->_value;
	}
	
	/**
	 * Generic getter
	 * 
	 * @access public
	 * @param string $key wanted key
	 * @since 1.0.5
	 */
	public function __get($key)
	{
		return (empty($this->_value[$key])) ? "" : $this->_value[$key];
	}
		
	/**
	 * Generic setter
	 * 
	 * @access public
	 * @param string $key given key
	 * @param string $value given value (if empty, key'll deleted)
	 * @return bool true if ok, else false
	 * @since 1.0.5
	 */
	public function __set($key,$value="")
	{
		if (empty($value))
			return $this->flush($key);
		
		$this->_value[$key] = $value;
		
		return true;
	}
	
	/**
	 * @access public
	 * @param string $key given key	 
	 * @return bool true if deleted, else false
	 * @since 1.0.8
	 */
	public function flush($key)
	{
		if (!isset($this->_value[$key]))
			return false;
			
		unset($this->_value[$key]);
		return true;
	}
	
	/**
	 * Save current cookie
	 * 
	 * @access public
	 * @param int $expire time (timestamp) the cookie expires
	 * @param string $path the path on the server in which the cookie will be available on
	 * @param string $domain the domain that the cookie is available to
	 * @return bool if output exists prior to calling this function, setcookie() will return false, else true
	 * @since 1.0.5
	 */
	public function save($expire=0, $path = "/", $domain = "")
	{
		return setcookie($this->_name,serialize($this->_value),$expire, $path, $domain);
	}
	
	/**
	 * Delete the current cookie
	 * 
	 * @access public
	 * @param string $path the path on the server in which the cookie will be available on
	 * @param string $domain the domain that the cookie is available to
	 * @return bool if output exists prior to calling this function, setcookie() will return false, else true
	 * @since 1.0.5
	 */
	public function delete($path = "/", $domain = "")
	{
    	return setcookie($this->_name, "", (time() - 3600), $path, $domain);
	}
}
?>