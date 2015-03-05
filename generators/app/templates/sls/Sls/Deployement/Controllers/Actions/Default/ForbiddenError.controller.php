<?php
/**
 * Action ForbiddenError Controller Default
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.Default.ControllerProtected
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultForbiddenError extends DefaultControllerProtected 
{	
	public function init()
	{
		parent::init();
	}
	
	/**
	 * Error 403
	 *
	 * @access public
	 */
	public function action() 
	{
		header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
		header("Status: 403 Forbidden");
	}	
}
?>