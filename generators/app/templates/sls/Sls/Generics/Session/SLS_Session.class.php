<?php
/**
 * SLS_Session class - Manage session
 *  
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Session
 * @since 1.0 
 */
class SLS_Session 
{
	private $_params;
	
	/**
	 * Constructor, start the session
	 *
	 * @access public
	 * @param bool $remote true if you want to access sillysmart in a isolated way like web-services, else false (default)
	 * @since 1.0
	 */
	public function __construct($remote=false)
	{
		$domainSession = SLS_Generic::getInstance()->getSiteConfig("domainSession");
		
		// If we have a session sharing between domains
		if (!empty($domainSession))
		{
			try
			{
				ini_set("session.cookie_domain",$domainSession);
			}
			catch (Exception $e)
			{
				SLS_Tracing::addTrace($e);
			}
		}
		
		// If not a remote access
		if (!$remote && PHP_SAPI !== 'cli')
			session_start();
	}
	
	/**
	 * Get the current session_id
	 * 
	 * @access public
	 * @return string $session_id current session_id
	 * @since 1.0.9
	 */
	public function session_id()
	{
		return session_id();
	}
	
	/**
	 * Regenerate the session
	 *
	 * @access public
	 * @since 1.0
	 */
	public function regenerate() 
	{
		session_regenerate_id();
	}
	
	/**
	 * Get a param from session	 
	 *
	 * @access public
	 * @param string $key the key you want
	 * @return string $value the value
	 * @see SLS_Session::getParams
	 * @see SLS_Session::setParam
	 * @see SLS_Session::getKey
	 * @since 1.0
	 */
	public function getParam($key)
	{
		if(isset($_SESSION[$key]))		
			return $_SESSION[$key];		
		else		
			return "";		
	}
	
	/**
	 * Get all params from session
	 *
	 * @access public	 
	 * @return array $sessionParams associative array
	 * <code>
	 * array(
	 * 		"key1" => "value1", 
	 * 		"keyN" => "valueN"
	 * )
	 * </code>
	 * @see SLS_Session::getParam
	 * @see SLS_Session::setParam
	 * @see SLS_Session::getKey
	 * @since 1.0
	 * @example 
	 * var_dump($this->_generic->getObjectSession()->getParams());
	 * // will produce :
	 * array(
  	 *		"current_lang"			=> "en"
  	 *		"current_side"			=> "user"
  	 *		"previousPage"			=> "/Home/Welcome.sls"
  	 *		"previousController"	=> "Home"
  	 *		"previousScontroller"	=> "Index"
  	 *		"previousMode"			=> "Home"
  	 *		"previousSmode"			=> "Welcome"
  	 *		"previousPost"			=> ""
  	 *		"previousMore"			=> ".sls"
	 * )
	 */
	public function getParams()
	{
		$sessionParams = array();
		
		foreach ($_SESSION as $key => $value)
			$sessionParams[$key] = $value;
			
		return $sessionParams;
	}
	
	/**
	 * Add a param in session	 
	 *
	 * @access public
	 * @param string $key the key to put
	 * @param string $value the value
	 * @see SLS_Session::getParam
	 * @see SLS_Session::getParams
	 * @see SLS_Session::getKey
	 * @since 1.0
	 */
	public function setParam($key, $value) 
	{
		$_SESSION[$key] = $value;
	}
	
	/**
	 * Get the keys associated with a value
	 *
	 * @access public
	 * @param sting $valuePost the value
	 * @return array $keys the keys
	 * @see SLS_Session::setParam
	 * @see SLS_Session::getParam
	 * @see SLS_Session::getParams
	 * @since 1.0
	 */
	public function getKey($valuePost) 
	{
		$return = array();
		foreach($_SESSION as $key=>$value)		
			if ($value === $valuePost)			
				array_push($return, $key);
		return $return;
	}
	
	/**
	 * Delete a value from the session
	 *
	 * @access public
	 * @param string $key the key to delete
	 * @return bool true if deleted, else false
	 * @since 1.0
	 */
	public function delParam($key) 
	{
		if (isset($_SESSION[$key]))
		{
			unset($_SESSION[$key]);
			return true;
		}
		return false;
	}
	
	/**
	 * Destroy the session
	 *
	 * @access public
	 * @since 1.0
	 */
	public function destroy() 
	{
		session_destroy();
	}
}
?>