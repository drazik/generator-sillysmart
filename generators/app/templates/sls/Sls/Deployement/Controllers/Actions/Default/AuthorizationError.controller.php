<?php
/**
 * Action AuthorizationError Controller Default
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.Default.ControllerProtected
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultAuthorizationError extends DefaultControllerProtected 
{	
	public function init()
	{
		parent::init();
	}
	
	/**
	 * Error 401
	 *
	 * @access public
	 */
	public function action() 
	{
		header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized"); 
		header("Status: 401 Unauthorized");
	}	
}
?>