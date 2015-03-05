<?php
/**
* Class ReportingBoStatus into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBoStatus extends SLS_BoControllerProtected
{
	public function action()
	{
		// Objects
		$this->useModel('Sls_graph',$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph";
		$slsGraph = new $className();
		$db = SLS_Sql::getInstance();
		$user = $this->hasAuthorative();
		
		// Params
		$slsGraphId = $this->_http->getParam('id');
		
		// Get graph
		if ($slsGraph->getModel($slsGraphId) === true)
		{			
			$slsGraph->setSlsGraphVisible(($slsGraph->__get("sls_graph_visible") == "yes") ? "no" : "yes");
			$slsGraph->save();
		}
		
		if ($this->_http->getParam("from") == "view")
			$this->_generic->forward("SLS_Bo","ReportingBoView",array("id" => $slsGraphId));
		else
			$this->_generic->forward("SLS_Bo","ReportingBo");
	}
}
?>