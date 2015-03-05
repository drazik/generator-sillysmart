<?php
class SLS_DefaultTemporaryRedirectError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error 307
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","TemporaryRedirectError"));
		}	
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 307 Temporary Redirect"); 
			header("Status: 307 Temporary Redirect");
		}
	}	
}
?>