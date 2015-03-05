<?php
/**
 * SLS_BoRights Tool - Check authentification and authorizations in customer back-office
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package SLS.Generics.Tools.SLS_Bo
 * @since 1.0 
 */
class SLS_BoRights
{
	/**
	 * Check if an admin is authorized to log in
	 *
	 * @access public static
	 * @param string $login login
	 * @param string $pwd password
	 * @return mixed 1 if connected, 0 if expired, -1 not logged, -2 disabled
	 * @since 1.0
	 */
	public static function connect($login,$pwd)
	{
		$generic = SLS_Generic::getInstance();
		$session = $generic->getObjectSession();
		$sessionToken = substr(substr(sha1($generic->getSiteConfig("privateKey")),12,31).substr(sha1($generic->getSiteConfig("privateKey")),4,11),6);
		
		$pathsHandle = file_get_contents($generic->getPathConfig("configSls")."/rights.xml");
		$xmlRights = new SLS_XMLToolbox($pathsHandle);
		$result = array_shift($xmlRights->getTagsAttributes("//sls_configs/entry[@login='".($login)."' and @password='".sha1($pwd)."' and count(action) > 0]",array("login")));
		
		if (!empty($result))
		{
			$attributes = array_shift($xmlRights->getTagsAttributes("//sls_configs/entry[@login='".($login)."' and @password='".sha1($pwd)."']",array("reset_pwd","renew_pwd","last_renew_pwd","name","firstname","last_connection","enabled")));
			
			$reset_pwd = $attributes["attributes"][0]["value"];
			$renew_pwd = $attributes["attributes"][1]["value"];
			$last_renew_pwd = $attributes["attributes"][2]["value"];
			$name = $attributes["attributes"][3]["value"];
			$firstname = $attributes["attributes"][4]["value"];
			$lastLogin = $attributes["attributes"][5]["value"];
			$enabled = $attributes["attributes"][6]["value"];
			
			if ($reset_pwd == "true" || (!empty($last_renew_pwd) && !empty($renew_pwd) && (strtotime("+".$renew_pwd, SLS_Date::dateToTimestamp($last_renew_pwd)) < SLS_Date::dateToTimestamp(date("Y-m-d")))))
				return 0;
			if ($enabled == "false")
				return -2;
				
			$xmlRights->setTagAttributes("//sls_configs/entry[@login='".$login."']",array("last_connection" => date("Y-m-d H:i:s")));
			$xmlRights->saveXML($generic->getPathConfig("configSls")."/rights.xml",$xmlRights->getXML());
			
			$session->setParam("SLS_BO_VALID_".$sessionToken,"true");	
			$session->setParam("SLS_BO_USER_".$sessionToken,$login);
			$session->setParam("SLS_BO_PREVIOUS_LOGIN",$lastLogin);
			$session->setParam("SLS_BO_LOGGED","true");
			$session->setParam("SLS_BO_USER",$login);
			$session->setParam("SLS_BO_USER_NAME",$name);
			$session->setParam("SLS_BO_USER_FIRSTNAME",$firstname);
			$session->setParam("ckfinderAuthorized",true);
			return 1;
		}
		else
			return -1;
	}
	
	/**
	 * Disconnect an admin
	 * 
	 * @access public static
	 * @since 1.0
	 */
	public static function disconnect()
	{		
		$generic = SLS_Generic::getInstance();
		$session = $generic->getObjectSession();
		$sessionToken = substr(substr(sha1($generic->getSiteConfig("privateKey")),12,31).substr(sha1($generic->getSiteConfig("privateKey")),4,11),6);
		
        $session->delParam("SLS_BO_VALID_".$sessionToken);
        $session->delParam("SLS_BO_USER_".$sessionToken);
        $session->delParam("SLS_BO_LOGGED");
        $session->delParam("SLS_BO_USER");        
		$session->delParam("SLS_BO_USER_NAME");
		$session->delParam("SLS_BO_USER_FIRSTNAME");
        $session->delParam("ckfinderAuthorized");
	}
	
	/**
	 * Check if an admin is logged
	 *
	 * @access public static
	 * @return bool true if logged, else false
	 * @since 1.0
	 */
	public static function isLogged()
	{
		$generic = SLS_Generic::getInstance();
		$session = $generic->getObjectSession();		
		$sessionToken = substr(substr(sha1($generic->getSiteConfig("privateKey")),12,31).substr(sha1($generic->getSiteConfig("privateKey")),4,11),6);
		
		if ($session->getParam("SLS_SESSION_VALID_".$sessionToken) == "true")
		{
			$session->setParam("SLS_BO_VALID_".$sessionToken,"true");
			$session->setParam("SLS_BO_LOGGED","true");
			$session->setParam("SLS_BO_USER_".$sessionToken,$session->getParam('SLS_SESSION_USER_'.$sessionToken));
			$session->setParam("SLS_BO_USER",$session->getParam('SLS_SESSION_USER_'.$sessionToken));
			$session->setParam("ckfinderAuthorized",true);
		}		
		if ($session->getParam("SLS_BO_VALID_".$sessionToken) == "true")
			return true;
		else
			return false;
	}
	
	/**
	 * Get the type of admin (admin|developer)
	 * 
	 * @access public static
	 * @return mixed string (admin|developer) if logged, else false
	 * @since 1.1
	 */
	public static function getAdminType()
	{
		if (!self::isLogged())
			return false;
			
		$generic = SLS_Generic::getInstance();
		$session = $generic->getObjectSession();		
		$sessionToken = substr(substr(sha1($generic->getSiteConfig("privateKey")),12,31).substr(sha1($generic->getSiteConfig("privateKey")),4,11),6);
		if ($session->getParam("SLS_SESSION_VALID_".$sessionToken) == "true")
			return "developer";
		else if ($session->getParam("SLS_BO_VALID_".$sessionToken) == "true")
			return "admin";
		else
			return false;
	}
	
	/**
	 * Check if the current admin like the given back-office action
	 *
	 * @access public static
	 * @param string $type the type of action ('List'|'Add'|'Modify'|'Delete')
	 * @return bool true if like, else false
	 * @since 1.0
	 */
	public static function isLike($role="read",$entity="",$aid="")
	{		
		$generic = SLS_Generic::getInstance();
		$session = $generic->getObjectSession();
		
		$sessionToken = substr(substr(sha1($generic->getSiteConfig("privateKey")),12,31).substr(sha1($generic->getSiteConfig("privateKey")),4,11),6);
		$xmlRights = new SLS_XMLToolbox(file_get_contents($generic->getPathConfig("configSls")."/rights.xml"));
						
		if (self::isAuthorized($role,$entity,$aid))
		{			
			if ($generic->actionIdExists($aid))
			{				
				$result = array_shift($xmlRights->getTags("//sls_configs/entry[@login='".($session->getParam("SLS_BO_USER"))."']/action[@id='".$aid."']/@like"));
				$like = (!empty($result) && $result == "true") ? true : false;
			}
			else
			{
				$result = array_shift($xmlRights->getTags("//sls_configs/entry[@login='".($session->getParam("SLS_BO_USER"))."']/action[@role='".$role."' and @entity='".$entity."']/@like"));				
				$like = (!empty($result) && $result == "true") ? true : false;
			}
			
			return $like;
		}
		else
			return false;
	}
	
	/**
	 * Check if the current admin is authorized to access on this back-office action
	 *
	 * @access public static
	 * @param string $type the type of action ('List'|'Add'|'Modify'|'Delete')
	 * @return int $authorized -1 if not logged, 0 is not authorized, 1 if ok
	 * @since 1.0
	 */
	public static function isAuthorized($role="read",$entity="",$aid="")
	{		
		$generic = SLS_Generic::getInstance();
		$session = $generic->getObjectSession();
		
		$sessionToken = substr(substr(sha1($generic->getSiteConfig("privateKey")),12,31).substr(sha1($generic->getSiteConfig("privateKey")),4,11),6);
		$xmlRights = new SLS_XMLToolbox(file_get_contents($generic->getPathConfig("configSls")."/rights.xml"));
		$authorized = 0;
				
		if (self::isLogged())
		{	
			if ($session->getParam("SLS_SESSION_VALID_".$sessionToken) == "true")
				return 1;
			
			if ($role == "dashboard" && SLS_String::contains($aid,"sls_graph"))
			{
				$result = array_shift($xmlRights->getTags("//sls_configs/entry[@login='".($session->getParam("SLS_BO_USER"))."']/action[@id='".$aid."']/@id"));
				$authorized = (!empty($result)) ? 1 : 0;
			}
			else if ($generic->actionIdExists($aid))
			{				
				$result = array_shift($xmlRights->getTags("//sls_configs/entry[@login='".($session->getParam("SLS_BO_USER"))."']/action[@id='".$aid."']/@id"));
				$authorized = (!empty($result)) ? 1 : 0;
			}
			else
			{
				$result = array_shift($xmlRights->getTags("//sls_configs/entry[@login='".($session->getParam("SLS_BO_USER"))."']/action[@role='".$role."' and @entity='".$entity."']/@id"));				
				$authorized = (!empty($result)) ? 1 : 0;
			}
			
			return $authorized;
		}
		else
			return -1;
	}
}
?>