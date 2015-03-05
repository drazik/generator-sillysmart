<?php
class SLS_DefaultForbiddenError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error 403
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","ForbiddenError"));
		}
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
			header("Status: 403 Forbidden");
		}
	}	
}
?>