<?php
class SLS_DefaultBadRequestError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error 400
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","BadRequestError"));
		}
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request"); 
			header("Status: 400 Bad Request");
		}
	}	
}
?>