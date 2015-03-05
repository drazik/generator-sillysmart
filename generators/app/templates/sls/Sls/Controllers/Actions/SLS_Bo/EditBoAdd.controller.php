<?php
class SLS_BoEditBoAdd extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		$redirect		= true;		
		$param	 		= $this->_http->getParam("name");		
		$model			= SLS_String::substrAfterFirstDelimiter($param,"_");
		$alias			= SLS_String::substrBeforeFirstDelimiter($param,"_");				
		$checkbox		= $this->_http->getParam("redirect");
		
		if (empty($checkbox))
			$redirect = false;
		
		$controllersXML = $this->_generic->getControllersXML();
		$controller 	= array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/@name"));
		
		$this->createActionBoAdd($controller,$model,$alias,$redirect);
		
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","EditBo");
		$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"]."/name/".$param.".sls");
	}
	
}
?>