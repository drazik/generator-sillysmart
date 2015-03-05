<?php
/**
* Class generic for the controller {{USER_BO}}
* Write here all your generic functions you need in your {{USER_BO}} Actions
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*/
class {{USER_BO}}ControllerProtected extends SiteProtected
{
	public $_bo = null;
	
	/**
	 * Admin Pre-process
	 * 
	 * @see SiteProtected::init()
	 */
	public function init()
	{
		parent::init();
		$this->_bo = new __SLS_Bo();
	}
}
?>