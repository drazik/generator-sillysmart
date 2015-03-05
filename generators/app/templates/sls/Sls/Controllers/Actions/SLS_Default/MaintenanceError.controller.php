<?php
class SLS_DefaultMaintenanceError extends SLS_DefaultControllerProtected 
{	
	/**
	 * Error 302
	 *
	 * @access public
	 */
	public function action() 
	{
		if (array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$this->_generic->getObjectSession()->getParam("previousController")."']/@side")) == "user")
		{
			$this->_generic->setSide("user");
			$this->_generic->redirect($this->_generic->getFullUrl("Default","MaintenanceError"));
		}	
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 302 Found"); 
			header("Status: 307 Found");
		}
	}	
}
?>