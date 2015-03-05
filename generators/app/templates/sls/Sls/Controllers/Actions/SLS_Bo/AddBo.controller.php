<?php
/**
* Class AddBo into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoAddBo extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		
		$controllersXML = $this->_generic->getControllersXML();
		$controller = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/@name"));
		
		if (!empty($controller))
		{
			$param = $this->_http->getParam("name");
			$model = SLS_String::substrAfterFirstDelimiter($param,"_");
			$alias = SLS_String::substrBeforeFirstDelimiter($param,"_");			
			$type = ucfirst($this->_http->getParam("type"));
			
			$actionTypes = array("List","Add","Modify","Delete","Clone","Email");
			
			if (in_array($type,$actionTypes))			
				$this->{createActionBo.$type}($controller,$model,$alias);			
		}
		
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","Bo");
		$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"]);
	}
}
?>