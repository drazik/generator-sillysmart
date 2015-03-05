<?php
class SLS_DefaultInternalServerError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error 500
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","InternalServerError"));
		}
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error"); 
			header("Status: 500 Internal Server Error");
		}
	}	
}
?>