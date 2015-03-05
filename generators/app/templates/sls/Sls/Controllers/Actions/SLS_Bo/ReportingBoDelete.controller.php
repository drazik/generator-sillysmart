<?php
/**
* Class ReportingBoDelete into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBoDelete extends SLS_BoControllerProtected
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
			$sql = "DELETE
						g, 
						gq, 
						gqc, 
						gqj, 
						gqw, 
						gqg, 
						gqo, 
						gql 
					FROM
						sls_graph g LEFT JOIN 
						sls_graph_query gq ON (g.sls_graph_query_id = gq.sls_graph_query_id) LEFT JOIN 
						sls_graph_query_column gqc ON (gq.sls_graph_query_id = gqc.sls_graph_query_id) LEFT JOIN
						sls_graph_query_join gqj ON (gq.sls_graph_query_id = gqj.sls_graph_query_id) LEFT JOIN
						sls_graph_query_where gqw ON (gq.sls_graph_query_id = gqw.sls_graph_query_id) LEFT JOIN
						sls_graph_query_group gqg ON (gq.sls_graph_query_id = gqg.sls_graph_query_id) LEFT JOIN
						sls_graph_query_order gqo ON (gq.sls_graph_query_id = gqo.sls_graph_query_id) LEFT JOIN  
						sls_graph_query_limit gql ON (gq.sls_graph_query_id = gql.sls_graph_query_id) 
					WHERE
						g.sls_graph_id = ".$db->quote($slsGraphId)." ";
			$db->delete($sql);
		}
		
		$this->_generic->forward("SLS_Bo","ReportingBo");
	}
}
?>