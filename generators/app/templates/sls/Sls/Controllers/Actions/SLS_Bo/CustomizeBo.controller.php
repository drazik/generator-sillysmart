<?php
class SLS_BoCustomizeBo extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		$param	 		= $this->_http->getParam("class");		
		$model			= SLS_String::substrAfterFirstDelimiter($param,"_");
		$alias			= SLS_String::substrBeforeFirstDelimiter($param,"_");		
		$filters 		= $this->_http->getParam("filters");
		$columns 		= $this->_http->getParam("columns");
		$column 		= $this->_http->getParam("column");
		$group	 		= $this->_http->getParam("group");
		$order 			= $this->_http->getParam("order");
		$start	 		= $this->_http->getParam("start");
		$length 		= $this->_http->getParam("length");
		$action_add 	= $this->_http->getParam("action_add");
		$action_modify 	= $this->_http->getParam("action_modify");
		$action_delete 	= $this->_http->getParam("action_delete");
		$action_clone 	= $this->_http->getParam("action_clone");
		$action_email 	= $this->_http->getParam("action_email");
		$join 			= $this->_http->getParam("join");
				
		if (!empty($order) && !empty($column))
		{
			$orderA = array("column" 	=> $column,
							"order"		=> $order);
		}
		else
			$orderA = array();
		if (is_numeric($start) && is_numeric($length) && $start >= 0 && $length > 0)
		{
			$limitA = array("start" 	=> $start,
							"length" 	=> $length);
		}
		else
			$limitA = array();
			
		if (is_array($join))
		{
			$newJoin = array();
			foreach($join as $cur_join)
			{
				$this->_generic->useModel(SLS_String::tableToClass($cur_join),$alias,"user");
				$class = ucfirst($alias)."_".SLS_String::tableToClass($cur_join);
				$object = new $class();
				$newJoin[] = array("table" => $cur_join, "column" => $object->getPrimaryKey(), "mode" => "left");
			}
			$join = $newJoin;
		}
			
		$join = (empty($join)) ? "" : (is_array($join) ? $join : "");
		
		$actions = array("add" 		=> ($action_add 	== "true") 	? true : false,
						 "modify" 	=> ($action_modify 	== "true") 	? true : false,
						 "delete" 	=> ($action_delete 	== "true") 	? true : false,
						 "clone" 	=> ($action_clone 	== "true") 	? true : false,
						 "email" 	=> ($action_email 	== "true") 	? true : false);
		
		$controllersXML = $this->_generic->getControllersXML();
		$controller 	= array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/@name"));
		
		$this->createActionBoList($controller,$model,$alias,$columns,$filters,$group,$orderA,$limitA,$join,$actions);
		
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","EditBo");
		$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"]."/name/".$param.".sls");
	}
	
}
?>