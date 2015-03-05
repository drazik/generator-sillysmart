<?php
/**
 * SLS_MemberSession class - Manage Member session
 *  
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Session
 * @since 1.0 
 */
class SLS_MemberSession
{
	private static $_instance;
	private $_generic;
	private $_session;	
	private $_login = "";
	private $_account_id = 0;
	
	/**
	 * Constructor
	 *
	 * @access public	 
	 * @since 1.0	 
	 */
	public function __construct() 
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_session = $this->_generic->getObjectSession();		
	}
	
	/**
	 * Singleton
	 *
	 * @access public static	 
	 * @return SLS_MemberSession $instance SLS_MemberSession instance
	 * @since 1.0
	 */
	public static function getInstance() 
	{
		if (is_null(self::$_instance))
			self::$_instance = new SLS_MemberSession();		
		return self::$_instance;
	}
	
	/**
	 * Tracking - Set the previous page	 
	 *
	 * @access public
	 * @since 1.0
	 */
	public function setTrackingPage() 
	{
		$http = $this->_generic->getObjectHttpRequest();
		$params = $http->getParams();
		$strPost = "";
		foreach($params as $key => $value)
			if ($key != "mode" && $key != "smode" && !empty($value))
				$strPost .= $key."/".$value."/";
		
		if ($this->_generic->getGenericControllerName() == $this->_generic->getBo() &&
			$this->_generic->getGenericScontrollerName() == "BoMenu")
		{
			// Nothing
		}
		else
		{
			$this->_session->setParam("previousPage", $_SERVER['REQUEST_URI']);
			$this->_session->setParam("previousController", $this->_generic->getGenericControllerName());
			$this->_session->setParam("previousScontroller", $this->_generic->getGenericScontrollerName());
			$this->_session->setParam("previousMode", $http->getParam("mode"));
			$this->_session->setParam("previousSmode", $http->getParam("smode"));
			$this->_session->setParam("previousPost", $strPost);
			$this->_session->setParam("previousMore", substr($_SERVER['QUERY_STRING'],strlen("mode=".$http->getParam("mode")."&smode=".$http->getParam("smode"))));
		}
	}
	
	/**
	 * Tracking - Get the previous page
	 *
	 * @access public
	 * @return string
	 * @since 1.0
	 */
	public function getPreviousPage()
	{
		return $this->_session->getParam("previousPage");
	}
}
?>