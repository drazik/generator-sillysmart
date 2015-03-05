<?php
/**
 * Action TemporaryRedirectError Controller Default
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.Default.ControllerProtected
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultTemporaryRedirectError extends DefaultControllerProtected 
{	
	public function init()
	{
		parent::init();
	}
	
	/**
	 * Error 307
	 *
	 * @access public
	 */
	public function action() 
	{
		header($_SERVER["SERVER_PROTOCOL"]." 307 Temporary Redirect"); 
		header("Status: 307 Temporary Redirect");
	}	
}
?>