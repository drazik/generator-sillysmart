<?php
/**
 * Action InternalServerError Controller Default
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.Default.ControllerProtected
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultInternalServerError extends DefaultControllerProtected 
{	
	public function init()
	{
		parent::init();
	}
	
	/**
	 * Error 500
	 *
	 * @access public
	 */
	public function action() 
	{
		header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error"); 
		header("Status: 500 Internal Server Error");
	}	
}
?>