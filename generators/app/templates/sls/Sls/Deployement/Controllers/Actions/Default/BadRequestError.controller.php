<?php
/**
 * Action BadRequest Controller Default
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.Default.ControllerProtected
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultBadRequestError extends DefaultControllerProtected 
{	
	public function init()
	{
		parent::init();
	}
	
	/**
	 * Error 400
	 *
	 * @access public
	 */
	public function action() 
	{
		header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request"); 
		header("Status: 400 Bad Request");
	}	
}
?>