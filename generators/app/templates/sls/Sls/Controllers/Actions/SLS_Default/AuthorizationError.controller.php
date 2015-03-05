<?php
class SLS_DefaultAuthorizationError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error 401
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","AuthorizationError"));
		}	
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized"); 
			header("Status: 401 Unauthorized");
		}
	}	
}
?>