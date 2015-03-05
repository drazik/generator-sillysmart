<?php
/**
* Class ReportingBoView into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBoView extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		$graphId = $this->_http->getParam('id');
		
		$this->_generic->useModel("Sls_graph",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph";
		$graph = new $className();
		
		if ($graph->getModel($graphId) === true)
			$this->getSlsGraphXml($xml, $graph);

		$xml->addFullTag("url_report",$this->_generic->getFullPath("SLS_Bo","ReportingBo"),true);
		$xml->addFullTag("url_edit",$this->_generic->getFullPath("SLS_Bo","ReportingBoEdit",array("id" => $graphId)),true);
		$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","ReportingBoDelete",array("id" => $graphId),true));
		$xml->addFullTag("url_status",$this->_generic->getFullPath("SLS_Bo","ReportingBoStatus",array("id" => $graphId, "from" => "view")),true);

		$this->saveXML($xml);
	}
	
	public function getSlsGraphXml(&$xml, $slsGraph)
	{
		$xml->startTag('sls_graph');
		if(empty($slsGraph))
		{
			$xml->endTag('sls_graph');
			return;
		}
		
		$colors = array(
			'#ff275f',
			'#980067',
			'#0094a5',
			'#96be12',
			'#ffba00',
			'#ed7807',
			'#ba3ecc',
			'#07b3da',
			'#408cd0',
			'#004e8e',
			'#f06eaa',
			'#a764a7',
			'#81c99b',
			'#abd273',
			'#faae5d',
			'#f16c4f',
			'#f4979c',
			'#bc8bbe',
			'#8292c9',
			'#67c4e8'
		);

		$labels = array('SLS_GRAPH_TYPE_PIE' => "Pie Chart",
						'SLS_GRAPH_TYPE_BAR' => "Bar Chart",
						'SLS_GRAPH_TYPE_PIVOT' => "Pivot Table",
						'SLS_GRAPH_TYPE_LIST' => "List",
						'SLS_AGGREGATION_TYPE_SUM' => "SUM",
						'SLS_AGGREGATION_TYPE_AVG' => "AVG",
						'SLS_AGGREGATION_TYPE_COUNT' => "COUNT",
						'SLS_AGGREGATION_TYPE_SUM_LABEL' => "Sum",
						'SLS_AGGREGATION_TYPE_AVG_LABEL' => "Average",
						'SLS_AGGREGATION_TYPE_COUNT_LABEL' => "Total",
						'SLS_QUERY_OPERATOR_LIKE' => "LIKE",
						'SLS_QUERY_OPERATOR_NOTLIKE' => "NOT LIKE",
						'SLS_QUERY_OPERATOR_STARTWITH' => "START WITH",
						'SLS_QUERY_OPERATOR_ENDWITH' => "END WITH",
						'SLS_QUERY_OPERATOR_EQUAL' => "EQUAL",
						'SLS_QUERY_OPERATOR_NOTEQUAL' => "NOT EQUAL",
						'SLS_QUERY_OPERATOR_IN' => "IN",
						'SLS_QUERY_OPERATOR_NOTIN' => "NOT IN",
						'SLS_QUERY_OPERATOR_LT' => "LESS THAN",
						'SLS_QUERY_OPERATOR_LTE' => "LESS THAN EQUAL",
						'SLS_QUERY_OPERATOR_GT' => "GREATER THAN",
						'SLS_QUERY_OPERATOR_GTE' => "GREATER THAN EQUAL",
						'SLS_QUERY_OPERATOR_NULL' => "IS NULL",
						'SLS_QUERY_OPERATOR_NOTNULL' => "IS NOT NULL");
		
		foreach ($slsGraph->getParams() as $key => $value)
		{
			if($key =='sls_graph_date_add')
			{
				if($value != '0000-00-00 00:00:00')
				{
					$date = new SLS_Date($value);
					$value = $date->getDate('DATE');
				}
				else
					$value = '-';
			}
			else if($key == 'sls_graph_type')
				$xml->addFullTag($key.'_label', $labels['SLS_GRAPH_TYPE_'.mb_strtoupper($value, 'UTF-8')], true);

			$xml->addFullTag($key, $value, true);
		}

		$this->_generic->useModel("Sls_graph_query",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query";
		$slsGraphQuery = new $className();
		$slsGraphQuery->getModel($slsGraph->__get("sls_graph_query_id"));
		$request = $this->getSlsGraphQueryRequest($slsGraphQuery->sls_graph_query_id);
		$requestRender = str_replace(array("\t"),array(str_repeat(" ",4)),$request);		
		
		$xml->addFullTag('sls_graph_query', $requestRender, true);

		$this->sql->changeDb($slsGraphQuery->__get("sls_graph_query_db_alias"));
		$results = $this->sql->select($request);
		if($results === false)
		{
			$xml->addFullTag('sls_graph_error', 'Invalid SQL query',true);
		}
		else
		{
			if($slsGraph->sls_graph_type == 'pie')
			{
				$xml->startTag('sls_graph_data');

				$this->_generic->useModel("Sls_graph_query_group",$this->defaultDb,"sls");
				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup = new $className();
				$groups = $slsGraphQueryGroup->searchModels("sls_graph_query_group",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQuery->sls_graph_query_id,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_group_id","order"=>"asc")));
				$columnGroupName = $groups[0]->sls_graph_query_group_column;

				foreach($results as $result)
				{
					$xml->startTag('sls_graph_data_line');
					$xml->addFullTag('sls_graph_data_legend', (!empty($result->legend)) ? $result->legend : "Unknown", true);
					$xml->addFullTag('sls_graph_data_count', $result->count, true);
					$xml->endTag('sls_graph_data_line');
				}
				$xml->endTag('sls_graph_data');
			}
			else if($slsGraph->sls_graph_type == 'bar')
			{
				$this->_generic->useModel("Sls_graph_query_group",$this->defaultDb,"sls");
				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup = new $className();
				$this->_generic->useModel("Sls_graph_query_column",$this->defaultDb,"sls");
				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				$slsGraphQueryColumn = new $className();

				$xml->startTag('sls_graph_data');

				$groups = $slsGraphQueryGroup->searchModels("sls_graph_query_group",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQuery->sls_graph_query_id,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_group_id","order"=>"asc")));
				$columnAggregation = array_shift($slsGraphQueryColumn->searchModels("sls_graph_query_column",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQuery->sls_graph_query_id,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_column_id","order"=>"asc"))));
				$columnAggregationFunction = $columnAggregation->sls_graph_query_column_aggregation;
				$columnAggregationName = $columnAggregation->sls_graph_query_column_name;
				$columnGroupName = $groups[0]->sls_graph_query_group_column;
				if(count($groups) > 1)
					$columnStackedName = $groups[1]->sls_graph_query_group_column;
				else
					$columnStackedName = '';

				if ($slsGraphQuery->sls_graph_query_db_alias != $this->sql->getCurrentDb())
					$this->sql->changeDb($slsGraphQuery->sls_graph_query_db_alias);
				$columnsComment = $this->sql->showColumns($slsGraphQuery->sls_graph_query_table);
				$tablesComment = $this->sql->showTables();

				$columnGroupComment = $this->array_pdo_search($columnsComment, 'Field', $columnGroupName)->Comment;
				$columnAggregationComment = $this->array_pdo_search($columnsComment, 'Field', $columnAggregationName)->Comment;

				if(!empty($columnStackedName))
					$columnStackedComment = $this->array_pdo_search($columnsComment, 'Field', $columnStackedName)->Comment;
				else
					$columnStackedComment = '';
				$tableComment = $this->array_pdo_search($tablesComment, 'Name', $slsGraphQuery->sls_graph_query_table)->Comment;

				$xml->addFullTag('sls_graph_data_aggregation_function', $columnAggregationFunction, true);

				$xml->addFullTag('sls_graph_data_legend_y', ($columnAggregationFunction == 'count' ? $tableComment : ($columnAggregationComment).' - '.$labels['SLS_AGGREGATION_TYPE_'.strtoupper($columnAggregationFunction).'_LABEL']), true);
				$xml->addFullTag('sls_graph_data_legend_x', $columnGroupComment, true);
				$xml->addFullTag('sls_graph_data_legend_stacked', $columnStackedComment, true);
				$xml->addFullTag('sls_graph_data_stacked', empty($columnStackedName) ? 'false' : 'true', true);

				$resultsGroup = array();
				$resultsGroupId = array();
				foreach($results as $result)
				{
					if(!in_array($result->legend_group_id, $resultsGroupId))
					{
						array_push($resultsGroup, $result);
						array_push($resultsGroupId, $result->legend_group_id);
					}
				}

				foreach($resultsGroup as $resultGroup)
				{
					$xml->startTag('sls_graph_data_line');
					$xml->addFullTag('sls_graph_data_legend', (!empty($resultGroup->legend_group)) ? $resultGroup->legend_group : "Unknown",true);
					$xml->addFullTag('sls_graph_data_value', round($resultGroup->value, 2),true);

					if(!empty($columnStackedName))
					{
						$xml->startTag('sls_graph_sub_data');
						foreach($results as $result)
						{
							if($result->legend_group == $resultGroup->legend_group)
							{
								$xml->startTag('sls_graph_sub_data_line');
								$xml->addFullTag('sls_graph_sub_data_legend', $result->legend_stacked, true);
								$xml->addFullTag('sls_graph_sub_data_value', round($result->value, 2), true);
								$xml->endTag('sls_graph_sub_data_line');
							}
						}
						$xml->endTag('sls_graph_sub_data');
					}

					$xml->endTag('sls_graph_data_line');
				}

				$xml->endTag('sls_graph_data');
				
			}
			else if($slsGraph->sls_graph_type == 'pivot')
			{
				$this->_generic->useModel("Sls_graph_query_group",$this->defaultDb,"sls");
				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup = new $className();
				$this->_generic->useModel("Sls_graph_query_column",$this->defaultDb,"sls");
				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				$slsGraphQueryColumn = new $className();

				$xml->startTag('sls_graph_data');

				$groups = $slsGraphQueryGroup->searchModels("sls_graph_query_group",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQuery->sls_graph_query_id,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_group_id","order"=>"asc")));
				$columnAggregation = array_shift($slsGraphQueryColumn->searchModels("sls_graph_query_column",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQuery->sls_graph_query_id,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_column_id","order"=>"asc"))));
				$columnAggregationFunction = $columnAggregation->sls_graph_query_column_aggregation;
				$columnAggregationName = $columnAggregation->sls_graph_query_column_name;
				$columnLineName = $groups[0]->sls_graph_query_group_column;
				$columnColumndName = $groups[1]->sls_graph_query_group_column;

				if ($slsGraphQuery->sls_graph_query_db_alias != $this->sql->getCurrentDb())
					$this->sql->changeDb($slsGraphQuery->sls_graph_query_db_alias);
				$columnsComment = $this->sql->showColumns($slsGraphQuery->sls_graph_query_table);
				$tablesComment = $this->sql->showTables();

				$columnGroupComment = $this->array_pdo_search($columnsComment, 'Field', $columnLineName)->Comment;
				$columnAggregationComment = $this->array_pdo_search($columnsComment, 'Field', $columnAggregationName)->Comment;
				$columnStackedComment = $this->array_pdo_search($columnsComment, 'Field', $columnColumndName)->Comment;

				$tableComment = $this->array_pdo_search($tablesComment, 'Name', $slsGraphQuery->sls_graph_query_table)->Comment;

				$resultsLines = array();
				$resultsLinesId = array();
				foreach($results as $result)
				{
					if(!in_array($result->legend_line_id, $resultsLinesId))
					{
						array_push($resultsLinesId, $result->legend_line_id);
						array_push($resultsLines, $result);
					}
				}

				$resultsColumns = array();
				$resultsColumnsId = array();
				foreach($results as $result)
				{
					if(!in_array($result->legend_column_id, $resultsColumnsId))
					{
						array_push($resultsColumnsId, $result->legend_column_id);
						array_push($resultsColumns, $result);
					}
				}

				foreach($resultsLines as $resultsLine)
				{
					$xml->startTag('sls_graph_data_line');
					$xml->addFullTag('sls_graph_data_legend',(empty($resultsLine->legend_line)) ? 'Unknown' : $resultsLine->legend_line, true);

					$xml->startTag('sls_graph_sub_data');
					foreach($resultsColumns as $resultsColumn)
					{
						$xml->startTag('sls_graph_sub_data_line');
						$result = $this->array_pdo_multiple_search($results, array('legend_line_id' => $resultsLine->legend_line_id, 'legend_column_id' => $resultsColumn->legend_column_id));
						$value = !empty($result) ? $result->value : 0;
						$xml->addFullTag('sls_graph_sub_data_legend', empty($resultsColumn->legend_column) ? 'Unknown' : $resultsColumn->legend_column, true);
						$xml->addFullTag('sls_graph_sub_data_value', round($value, 2), true);
						$xml->endTag('sls_graph_sub_data_line');
					}
					$xml->endTag('sls_graph_sub_data');
					$xml->endTag('sls_graph_data_line');
				}

				$xml->endTag('sls_graph_data');
			}
			else if($slsGraph->sls_graph_type == 'list')
			{
				$xml->startTag('sls_graph_data');

				foreach($results as $result)
				{
					$xml->startTag('sls_graph_data_line');
					$xml->startTag('sls_graph_sub_data');
					foreach($result as $key => $value)
					{
						$xml->startTag('sls_graph_sub_data_line');
							$xml->addFullTag('sls_graph_sub_data_legend', $key, true);
							$xml->addFullTag('sls_graph_sub_data_value', $value, true);
						$xml->endTag('sls_graph_sub_data_line');
					}
					$xml->endTag('sls_graph_sub_data');
					$xml->endTag('sls_graph_data_line');
				}
				$xml->endTag('sls_graph_data');
			}
		}
		
		$xml->startTag('sls_graph_colors');
			foreach($colors as $color)
				$xml->addFulLTag('sls_graph_color', $color, true);
		$xml->endTag('sls_graph_colors');

		$xml->endTag('sls_graph');
	}
	
	public function getSlsGraphQueryRequest($slsGraphQueryId)
	{
		$this->_generic->useModel("Sls_graph_query",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query";
		$slsGraphQuery = new $className();

		if($slsGraphQuery->getModel($slsGraphQueryId) === false)
			return null;

		$this->_generic->useModel("Sls_graph_query_column",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
		$slsGraphQueryColumn = new $className();
		
		$this->_generic->useModel("Sls_graph_query_join",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
		$slsGraphQueryJoin = new $className();
		
		$this->_generic->useModel("Sls_graph_query_where",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_where";
		$slsGraphQueryWhere = new $className();
		
		$this->_generic->useModel("Sls_graph_query_group",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
		$slsGraphQueryGroup = new $className();
		
		$this->_generic->useModel("Sls_graph_query_order",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_order";
		$slsGraphQueryOrder = new $className();
		
		$this->_generic->useModel("Sls_graph_query_limit",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_limit";
		$slsGraphQueryLimit = new $className();
		$db = SLS_Sql::getInstance();
		$render = "";

		$slsGraphQueryId = $slsGraphQuery->sls_graph_query_id;
		$slsGraphQueryTableAlias = $slsGraphQuery->sls_graph_query_table_alias;
		$tables = array($slsGraphQuery->sls_graph_query_table => !empty($slsGraphQueryTableAlias) ? $slsGraphQuery->sls_graph_query_table_alias : $slsGraphQuery->sls_graph_query_table);
		$columns = $slsGraphQueryColumn->searchModels("sls_graph_query_column",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQueryId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_column_id","order"=>"asc")));
		$joins = $slsGraphQueryJoin->searchModels("sls_graph_query_join",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQueryId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_join_id","order"=>"asc")));
		$wheres = $slsGraphQueryWhere->searchModels("sls_graph_query_where",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQueryId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_where_id","order"=>"asc")));

		if (!empty($joins))
		{
			for($j=0 ; $j<$countJ=count($joins) ; $j++)
			{
				$tables[$joins[$j]->sls_graph_query_join_table] = $joins[$j]->sls_graph_query_join_table_alias;
			}
		}

		$whereRoot = null;
		if(!empty($wheres))
			$whereRoot = $wheres[0];

		$groups = $slsGraphQueryGroup->searchModels("sls_graph_query_group",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQueryId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_group_id","order"=>"asc")));
		$orders = $slsGraphQueryOrder->searchModels("sls_graph_query_order",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQueryId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_order_id","order"=>"asc")));
		$limit = array_shift($slsGraphQueryLimit->searchModels("sls_graph_query_limit",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$slsGraphQueryId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_limit_id","order"=>"asc"))));

		$render = '';

		// SELECT
		$sql = "SELECT "."\n";
		$nbColumns = count($columns);

		$columnsAggregation = array_filter($columns, array($this,'filterAggregate'));

		if (!empty($columns))
		{
			if($nbColumns == 1 && !empty($columnsAggregation))
				$sql .= "\t"."*, ";

			for($j=0 ; $j<$countJ=count($columns) ; $j++)
			{
				if(!empty($columns[$j]->sls_graph_query_column_aggregation))
					$sql .= "\t".strtoupper($columns[$j]->sls_graph_query_column_aggregation)."(".(empty($columns[$j]->sls_graph_query_column_name) ? '*' : ($columns[$j]->sls_graph_query_column_table_alias).".`".$columns[$j]->sls_graph_query_column_name."`").")";
				else if(!SLS_String::contains($columns[$j]->sls_graph_query_column_name, 'CONCAT('))
				{
					$columnDateFormat = false;
					if ($columns[$j]->sls_graph_query_column_alias == "legend_group")
					{
						$columnInfos = $this->sql->showColumns($columns[$j]->sls_graph_query_column_table);
						for($i=0 ; $i<$count=count($columnInfos) ; $i++)
						{
							if ($columnInfos[$i]->Field == $columns[$j]->sls_graph_query_column_name)
							{
								if (SLS_String::contains($columnInfos[$i]->Type,"date") || SLS_String::contains($columnInfos[$i]->Type,"timestamp"))
									$columnDateFormat = true;
									
								break;
							}
						}
					}
					$sql .= "\t".(($columnDateFormat) ? "DATE_FORMAT(".($columns[$j]->sls_graph_query_column_table_alias).".`".$columns[$j]->sls_graph_query_column_name."`".",'%Y-%m')" : ($columns[$j]->sls_graph_query_column_table_alias).".`".$columns[$j]->sls_graph_query_column_name."`");
				}
				else
					$sql .= "\t".$columns[$j]->sls_graph_query_column_name;

				if (!empty($columns[$j]->sls_graph_query_column_alias))
					$sql .= " AS `".$columns[$j]->sls_graph_query_column_alias."`";
				$sql .= (($j < ($countJ-1)) ? ", " : " ")."\n";
			}

		}
		else
			$sql .= "\t"."* ";

		// FROM
		$sql .= "FROM "."\n";
		$sql .= "\t"."`".$slsGraphQuery->sls_graph_query_table."`"." ".$slsGraphQuery->sls_graph_query_table_alias." "."\n";

		// JOIN
		if (!empty($joins))
		{
			for($j=0 ; $j<$countJ=count($joins) ; $j++)
			{
				$sql .= "\t".strtoupper($joins[$j]->sls_graph_query_join_mode)." JOIN "."`".$joins[$j]->sls_graph_query_join_table_source."`"." ".$joins[$j]->sls_graph_query_join_table_alias_source." ON ".$joins[$j]->sls_graph_query_join_table_alias_target."."."`".$joins[$j]->sls_graph_query_join_column_target."`"." = ".$joins[$j]->sls_graph_query_join_table_alias_source."."."`".$joins[$j]->sls_graph_query_join_column_source."`";
				$sql .= (($j < ($countJ-1)) ? " " : " ")."\n";
			}
		}

		// WHERE
		if (!empty($whereRoot) && count($wheres) > 1)
		{
			$tab = 0;
			$sql .= "WHERE "."\n";
			$sql .= str_repeat("\t",$tab)."( "."\n";
			$sql .= $this->getSlsGraphQueryWheres($whereRoot->sls_graph_query_where_id,$tab+1,$tables,$slsGraphQueryId);
			$sql .= str_repeat("\t",$tab).") "."\n";
		}

		// GROUP
		if (!empty($groups))
		{
			$sql .= "GROUP BY "."\n";
			for($j=0 ; $j<$countJ=count($groups) ; $j++)
			{
				$join = $this->array_pdo_multiple_search($joins, array(
					'sls_graph_query_join_column_target' => $groups[$j]->sls_graph_query_group_column,
					'sls_graph_query_join_table_alias_target' => $groups[$j]->sls_graph_query_group_table_alias)
				);

				$groupAlias = "";
				$groupColumn = "";
				$groupTable = "";	
				$groupDateFormat = false;			
				if(empty($join))
				{
					$groupAlias = $groups[$j]->sls_graph_query_group_table_alias;
					$groupColumn = $groups[$j]->sls_graph_query_group_column;
					$groupTable = $groups[$j]->sls_graph_query_group_table;
				}
				else
				{
					$groupAlias = $join->sls_graph_query_join_table_alias_source;
					$groupColumn = $join->sls_graph_query_join_column_source;
					$groupTable = $join->sls_graph_query_join_table_source;
				}
				$columnGroupInfos = $this->sql->showColumns($groupTable);
				for($i=0 ; $i<$count=count($columnGroupInfos) ; $i++)
				{
					if ($columnGroupInfos[$i]->Field == $groupColumn)
					{
						if (SLS_String::contains($columnGroupInfos[$i]->Type,"date") || SLS_String::contains($columnGroupInfos[$i]->Type,"timestamp"))
							$groupDateFormat = true;
							
						break;
					}
				}
				$sql .= (($groupDateFormat) ? "\t"."DATE_FORMAT(".$groupAlias."."."`".$groupColumn."`, '%Y-%m')" : "\t".$groupAlias."."."`".$groupColumn."`");

				$sql .= (($j < ($countJ-1)) ? ", " : " ")."\n";
			}
		}

		// ORDER
		if (!empty($orders))
		{
			$sql .= "ORDER BY "."\n";
			for($j=0 ; $j<$countJ=count($orders) ; $j++)
			{
				$sql .= "\t".$orders[$j]->sls_graph_query_order_table_alias."."."`".$orders[$j]->sls_graph_query_order_column."`"." ".strtoupper($orders[$j]->sls_graph_query_order_way);
				$sql .= (($j < ($countJ-1)) ? ", " : " ")."\n";
			}
		}

		// LIMIT
		if (!empty($limit) && $limit->sls_graph_query_limit_start >= 0 && $limit->sls_graph_query_limit_length > 0)
		{
			$sql .= "LIMIT "."\n";
			$sql .= "\t".$limit->sls_graph_query_limit_start.", ".$limit->sls_graph_query_limit_length." "."\n";
		}
		return $sql;
	}
	
	public function getSlsGraphQueryWheres($parentId,$tab,$tables,$queryId)
	{
		$db = SLS_Sql::getInstance();

		$this->_generic->useModel("Sls_graph_query_where",$this->defaultDb,"sls");
		$className = ucfirst($this->defaultDb)."_Sls_graph_query_where";
		$slsGraphQueryWhere = new $className();
		$sql = "";
		$wheres = $slsGraphQueryWhere->searchModels("sls_graph_query_where",array(),array(0=>array("column"=>"sls_graph_query_id","value"=>$queryId,"mode"=>"equal"),1=>array("column"=>"parent_sls_graph_query_where_id","value"=>$parentId,"mode"=>"equal")),array(),array(array("column"=>"sls_graph_query_where_id","order"=>"asc")));
		for($j=0 ; $j<$countJ=count($wheres) ; $j++)
		{
			if ($j>0)
				$sql .= str_repeat("\t",$tab).strtoupper($wheres[$j]->sls_graph_query_where_condition)." "."\n";

			if ($wheres[$j]->sls_graph_query_where_type == "group")
			{
				$sql .= str_repeat("\t",$tab)."( "."\n";
				$sql .= $this->getSlsGraphQueryWheres($wheres[$j]->sls_graph_query_where_id,$tab+1,$tables,$queryId);
				$sql .= str_repeat("\t",$tab).") "."\n";
			}
			else
			{
				$sql .= str_repeat("\t",$tab).$wheres[$j]->sls_graph_query_where_table_alias."."."`".$wheres[$j]->sls_graph_query_where_column."`";
				switch ($wheres[$j]->sls_graph_query_where_operator)
				{
					case "like": 		$sql .= " LIKE ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "notlike": 	$sql .= " NOT LIKE ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n"; break;
					case "startwith": 	$sql .= " LIKE ".$db->quote("%".$wheres[$j]->sls_graph_query_where_value)." "."\n"; break;
					case "endwith": 	$sql .= " LIKE ".$db->quote($wheres[$j]->sls_graph_query_where_value."%")." "."\n"; break;
					case "equal": 		$sql .= " = ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "notequal": 	$sql .= " != ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "in": 			$sql .= " IN (".$db->quote($wheres[$j]->sls_graph_query_where_value).") "."\n";		break;
					case "notin": 		$sql .= " NOT IN (".$db->quote($wheres[$j]->sls_graph_query_where_value).") "."\n";	break;
					case "lt": 			$sql .= " < ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "lte": 		$sql .= " <= ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "gt": 			$sql .= " > ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "gte": 		$sql .= " >= ".$db->quote($wheres[$j]->sls_graph_query_where_value)." "."\n";		break;
					case "null": 		$sql .= " IS NULL "."\n";														break;
					case "notnull": 	$sql .= " IS NOT NULL "."\n";													break;
				}
			}
		}

		return $sql;
	}
	
	public function array_pdo_search($array, $key, $value)
	{
		$results = array();
		if (is_array($array))
		{
			foreach($array as $arrayLine)
			{
				if (isset($arrayLine->{$key}) && $arrayLine->{$key} == $value)
					return $arrayLine;
			}
		}
		return false;
	}
	
	public function array_pdo_multiple_search($array, $where)
	{
		if (is_array($array))
		{
			foreach($array as $pdo)
			{
				if($this->pdo_multiple_search($pdo, $where) == true)
					return $pdo;
			}
		}
		return false;
	}
	
	public function pdo_multiple_search($pdo, $where)
	{
		foreach($where as $key => $value)
		{
			if($this->pdo_search($pdo, $key, $value) == false)
				return false;
		}
	
		return true;
	}
	
	public function pdo_search($pdo, $key, $value)
	{
		if (isset($pdo->{$key}) && $pdo->{$key} == $value)
			return true;
		return false;
	}
	
	public function filterAggregate($e)
	{
		return !empty($e->sls_graph_query_column_aggregation);
	}
}
?>