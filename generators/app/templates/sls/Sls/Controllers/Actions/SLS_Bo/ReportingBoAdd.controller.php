<?php
/**
* Class ReportingBoAdd into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBoAdd extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);

		$sql = SLS_Sql::getInstance();

		$this->useModel('Sls_graph_query',$this->defaultDb,"sls");
		$this->useModel('Sls_graph',$this->defaultDb,"sls");
		$this->useModel('Sls_graph_query_column',$this->defaultDb,"sls");
		$this->useModel('Sls_graph_query_join',$this->defaultDb,"sls");
		$this->useModel('Sls_graph_query_group',$this->defaultDb,"sls");
		$this->useModel('Sls_graph_query_where',$this->defaultDb,"sls");

		$className = ucfirst($this->defaultDb)."_Sls_graph_query";
		$slsGraphQuery = new $className();
		$className = ucfirst($this->defaultDb)."_Sls_graph";
		$slsGraph = new $className();

		$errors = array();
		$slsGraphTypes = array('pie', 'bar', 'pivot', 'list');
		$slsGraphAggregationTypes = array('sum', 'avg', 'count');
		$slsGraphAggregationTypesNeedField = array('sum', 'avg');
		$slsGraphQueryOperators = array('like','notlike','startwith','endwith','equal','notequal','in','notin','lt','lte','gt','gte','null','notnull');
		$this->queryOperatorsNeedField = array('like','notlike','startwith','endwith','equal','notequal','in','notin','lt','lte','gt','gte');

		$tableFieldsValues = array();
		$slsGraphQueryData = array(
			'sls_graph_query_where' => array(
				'sls_graph_query_where_type'      => 'group',
				'sls_graph_query_where_condition' => '',
				'sls_graph_query_where_column'    => '',
				'sls_graph_query_where_operator'  => '',
				'sls_graph_query_where_value'     => '',
				'sls_graph_query_where_children'  => array(),
				'sls_graph_query_where_root'      => 'true',
			)
		);
		$slsGraphData = array();
		$slsGraphQueryWheres = array();

		# reload
		if($this->_http->getParam('reload') == 'true')
		{
			$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
			$slsGraphQueryData = $this->_http->getParam('sls_graph_query');
			$slsGraphData = $this->_http->getParam('sls_graph');

			if(!$slsGraph->setSlsGraphTitle($slsGraphData['sls_graph_title']))
				$errors['sls_graph_title'] = 'Titre invalide';

			if(!$slsGraph->setSlsGraphTitle($slsGraphData['sls_graph_title']))
				$errors['sls_graph_title'] = 'Titre invalide';

			$tmp = explode('.', $slsGraphQueryData['sls_graph_query_table']);

			if(count($tmp) == 2)
			{
				$slsGraphQueryDbAlias = $tmp[0];
				$slsGraphQueryTable = $tmp[1];
				$slsGraphQueryTableAlias = $this->getTableAlias($slsGraphQueryTable);
				$sql->changeDb($slsGraphQueryDbAlias);
			}
			else
			{
				$slsGraphQueryTableAlias = $slsGraphQueryDbAlias = $slsGraphQueryTable = '';
			}

			if(!$slsGraphQuery->setSlsGraphQueryDbAlias($slsGraphQueryDbAlias) || !$slsGraphQuery->setSlsGraphQueryTable($slsGraphQueryTable) || !$slsGraphQuery->setSlsGraphQueryTableAlias($slsGraphQueryTableAlias) || !$sql->tableExists($slsGraphQueryTable))
				$errors['sls_graph_query_table'] = 'Table invalide';
			else
			{
				$tableFields = $sql->showColumns($slsGraphQueryTable);
				$tableFieldsValues = array_map(array($this,'filterField'), $tableFields);
			}

			if(!$slsGraph->setSlsGraphType($slsGraphData['sls_graph_type']))
			{
				$errors['sls_graph_type'] = 'Type invalide';
			}
			else if($slsGraphData['sls_graph_type'] == 'pie')
			{

				# query columns
				$tmp = explode('.', $slsGraphData['sls_graph_pie_group_by']);
				$column = $tmp[1];
				$columnConcat = $column;

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				$slsGraphQueryColumn1 = new $className();
				$slsGraphQueryColumn1->setSlsGraphQueryColumnName($column);
				$slsGraphQueryColumn1->setSlsGraphQueryColumnAlias('legend_id');
				$slsGraphQueryColumn1->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				$slsGraphQueryColumn1->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);

				$columnFk = array_shift($xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($slsGraphQueryDbAlias.'_'.$slsGraphQueryTable)."' and @columnFk = '".$column."']",array("tablePk","labelPk")));
				if(!empty($columnFk))
				{
					$tablePk = $columnFk['attributes'][0]['value'];
					$labelPk = $columnFk['attributes'][1]['value'];

					$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tablePk, '_');
					$tablePk = SLS_String::substrAfterFirstDelimiter($tablePk, '_');

					$this->_generic->useModel($tablePk, $dbPk, "user");
					$classFk = ucfirst($dbPk)."_".SLS_String::tableToClass($tablePk);
					$objectFk = new $classFk();
					$columns = array();
					$columnsLabel = array();
					$clause = array();
					$render = array();

					$columnTable = $objectFk->getTable();

					# add join
					$i = 1;
					# target
					$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
					${slsGraphQueryJoin.$i} = new $className();
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableTarget($slsGraphQueryTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasTarget($slsGraphQueryTableAlias);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnTarget($column);
					# /target

					# source
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableSource($columnTable);
					$slsGraphQueryJoin = $this->getTableAlias($columnTable);
					/*$slsGraphQueryJoin = $columnTable.$aliasIndex;
					$aliasIndex++;*/
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasSource($slsGraphQueryJoin);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnSource($objectFk->getPrimaryKey());
					# /source

					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinMode('left');
					$i++;
					# /add join

					foreach($objectFk->getParams() as $key => $value)
					{
						array_push($columns,"`".$key."`");
						if (SLS_String::contains($labelPk,$key))
							$columnsLabel[$key] = strpos($labelPk,$key);
					}
					array_multisort($columnsLabel);


					foreach($columnsLabel as $columnLabel => $offset)
						array_push($clause,$columnLabel);

					$pattern = str_replace("'","''",$labelPk);

					foreach($clause as $columnC)
						$pattern = str_replace($columnC,"',"."CAST(".$slsGraphQueryJoin.".`".$columnC."` AS CHAR),'",$pattern);

					$columnConcat = "CONCAT('".$pattern."')";
				}

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				$slsGraphQueryColumn2 = new $className();
				$slsGraphQueryColumn2->setSlsGraphQueryColumnName($columnConcat);
				$slsGraphQueryColumn2->setSlsGraphQueryColumnAlias('legend');
				$slsGraphQueryColumn2->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				$slsGraphQueryColumn2->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				$slsGraphQueryColumn3 = new $className();
				$slsGraphQueryColumn3->setSlsGraphQueryColumnAggregation('count');
				$slsGraphQueryColumn3->setSlsGraphQueryColumnAlias('count');
				$slsGraphQueryColumn3->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				$slsGraphQueryColumn3->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				# /query columns

				# query groups
				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup1 = new $className();
				if(!$slsGraphQueryGroup1->setSlsGraphQueryGroupColumn($column)/* || !in_array($slsGraphData['sls_graph_pie_group_by'], $tableFieldsValues)*/)
					$errors['sls_graph_pie_group_by'] = 'Champ groupé invalide';
				# /query groups

				$sql->changeDb($slsGraphQueryDbAlias);
				$joins = $this->getQueryJoin($slsGraphQueryTable, $slsGraphQueryTableAlias, array($slsGraphData['sls_graph_pie_group_by']));
			}
			else if($slsGraphData['sls_graph_type'] == 'bar')
			{
				$i = 1;
				$j = 1;

				# query columns
				$tmp = explode('.', $slsGraphData['sls_graph_bar_aggregation_field']);
				if(count($tmp) == 2)
					$columnAggregationField = $tmp[1];
				else
					$columnAggregationField = '';

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				if(empty($slsGraphData['sls_graph_bar_aggregation'])
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAggregation($slsGraphData['sls_graph_bar_aggregation'])
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable)
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias)
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('value'))
					$errors['sls_graph_bar_aggregation'] = 'Aggrégation invalide';

				if(in_array($slsGraphData['sls_graph_bar_aggregation'], $slsGraphAggregationTypesNeedField) && (empty($columnAggregationField) ||  !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($columnAggregationField)))
					$errors['sls_graph_bar_aggregation_field'] = 'Champ aggrégation invalide';
				$j++;

				# query column group
				$tmp = explode('.', $slsGraphData['sls_graph_bar_group_by']);
				$column = $tmp[1];
				$columnConcat = $column;

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($column);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_group_id');
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				$j++;

				$columnFk = array_shift($xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($slsGraphQueryDbAlias.'_'.$slsGraphQueryTable)."' and @columnFk = '".$column."']",array("tablePk","labelPk")));
				if(!empty($columnFk))
				{
					$tablePk = $columnFk['attributes'][0]['value'];
					$labelPk = $columnFk['attributes'][1]['value'];

					$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tablePk, '_');
					$tablePk = SLS_String::substrAfterFirstDelimiter($tablePk, '_');

					$this->_generic->useModel($tablePk, $dbPk, "user");
					$classFk = ucfirst($dbPk)."_".SLS_String::tableToClass($tablePk);
					$objectFk = new $classFk();
					$columns = array();
					$columnsLabel = array();
					$clause = array();
					$render = array();

					$columnTable = $objectFk->getTable();

					# add join
					# target
					$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
					${slsGraphQueryJoin.$i} = new $className();
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableTarget($slsGraphQueryTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasTarget($slsGraphQueryTableAlias);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnTarget($column);
					# /target

					# source
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableSource($columnTable);
					$slsGraphQueryJoin = $this->getTableAlias($columnTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasSource($slsGraphQueryJoin);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnSource($objectFk->getPrimaryKey());
					# /source

					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinMode('left');
					$i++;
					# /add join

					foreach($objectFk->getParams() as $key => $value)
					{
						array_push($columns,"`".$key."`");
						if (SLS_String::contains($labelPk,$key))
							$columnsLabel[$key] = strpos($labelPk,$key);
					}
					array_multisort($columnsLabel);

					foreach($columnsLabel as $columnLabel => $offset)
						array_push($clause,$columnLabel);

					$pattern = str_replace("'","''",$labelPk);
					foreach($clause as $columnC)
						$pattern = str_replace($columnC,"',"."CAST(".$slsGraphQueryJoin.".`".$columnC."` AS CHAR),'",$pattern);

					$columnConcat = "CONCAT('".$pattern."')";
				}

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($columnConcat);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_group');
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				$j++;
				# query column group

				# query column stacked
				if(!empty($slsGraphData['sls_graph_bar_stacked_field']))
				{
					$tmp = explode('.', $slsGraphData['sls_graph_bar_stacked_field']);
					$column = $tmp[1];
					$columnConcat = $column;

					$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
					${slsGraphQueryColumn.$j} = new $className();
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($column);
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_stacked_id');
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
					$j++;

					$columnFk = array_shift($xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($slsGraphQueryDbAlias.'_'.$slsGraphQueryTable)."' and @columnFk = '".$column."']",array("tablePk","labelPk")));
					if(!empty($columnFk))
					{
						$tablePk = $columnFk['attributes'][0]['value'];
						$labelPk = $columnFk['attributes'][1]['value'];

						$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tablePk, '_');
						$tablePk = SLS_String::substrAfterFirstDelimiter($tablePk, '_');

						$this->_generic->useModel($tablePk, $dbPk, "user");
						$classFk = ucfirst($dbPk)."_".SLS_String::tableToClass($tablePk);
						$objectFk = new $classFk();
						$columns = array();
						$columnsLabel = array();
						$clause = array();
						$render = array();

						$columnTable = $objectFk->getTable();

						# add join
						# target
						$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
						${slsGraphQueryJoin.$i} = new $className();
						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableTarget($slsGraphQueryTable);
						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasTarget($slsGraphQueryTableAlias);
						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnTarget($column);
						# /target

						# source
						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableSource($columnTable);
						$slsGraphQueryJoin = $this->getTableAlias($columnTable);
						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasSource($slsGraphQueryJoin);
						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnSource($objectFk->getPrimaryKey());
						# /source

						${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinMode('left');
						$i++;
						# /add join

						foreach($objectFk->getParams() as $key => $value)
						{
							array_push($columns,"`".$key."`");
							if (SLS_String::contains($labelPk,$key))
								$columnsLabel[$key] = strpos($labelPk,$key);
						}
						array_multisort($columnsLabel);

						foreach($columnsLabel as $columnLabel => $offset)
							array_push($clause,$columnLabel);

						$pattern = str_replace("'","''",$labelPk);
						foreach($clause as $columnC)
							$pattern = str_replace($columnC,"',"."CAST(".$slsGraphQueryJoin.".`".$columnC."` AS CHAR),'",$pattern);

						$columnConcat = "CONCAT('".$pattern."')";
					}

					$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
					${slsGraphQueryColumn.$j} = new $className();
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($columnConcat);
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_stacked');
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
					${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				}
				# query column stacked
				# /query columns

				# query groups
				$tmp = explode('.', $slsGraphData['sls_graph_bar_group_by']);
				$columnGroupByField = $tmp[1];

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup1 = new $className();
				if(!$slsGraphQueryGroup1->setSlsGraphQueryGroupColumn($columnGroupByField))
					$errors['sls_graph_bar_group_by'] = 'Champ groupé invalide';

				if(!empty($slsGraphData['sls_graph_bar_stacked_field']))
				{
					$tmp = explode('.', $slsGraphData['sls_graph_bar_stacked_field']);
					$columnStackedField = $tmp[1];

					$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
					$slsGraphQueryGroup2 = new $className();
					if(!$slsGraphQueryGroup2->setSlsGraphQueryGroupColumn($columnStackedField))
						$errors['sls_graph_bar_stacked_field'] = 'Champ réservé invalide';
				}
				# /query groups

				$joins = $this->getQueryJoin($slsGraphQueryTable, $slsGraphQueryTableAlias, array($slsGraphData['sls_graph_bar_group_by']));
			}
			else if($slsGraphData['sls_graph_type'] == 'pivot')
			{
				$i = 1;
				$j = 1;
				# query columns
				$tmp = explode('.', $slsGraphData['sls_graph_pivot_aggregation_field']);
				if(count($tmp) == 2)
					$columnAggregationField = $tmp[1];
				else
					$columnAggregationField = '';

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				if(empty($slsGraphData['sls_graph_pivot_aggregation'])
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAggregation($slsGraphData['sls_graph_pivot_aggregation'])
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable)
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias)
					|| !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('value'))
					$errors['sls_graph_pivot_aggregation'] = 'Aggrégation invalide';

				if(in_array($slsGraphData['sls_graph_pivot_aggregation'], $slsGraphAggregationTypesNeedField) && (empty($columnAggregationField) ||  !${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($columnAggregationField)))
					$errors['sls_graph_pivot_aggregation_field'] = 'Champ aggrégation invalide';
				$j++;

				# query column line
				$tmp = explode('.', $slsGraphData['sls_graph_pivot_line']);
				$column = $tmp[1];
				$columnConcat = $tmp[1];

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($column);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_line_id');
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				$j++;

				$columnFk = array_shift($xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($slsGraphQueryDbAlias.'_'.$slsGraphQueryTable)."' and @columnFk = '".$column."']",array("tablePk","labelPk")));
				if(!empty($columnFk))
				{
					$tablePk = $columnFk['attributes'][0]['value'];
					$labelPk = $columnFk['attributes'][1]['value'];

					$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tablePk, '_');
					$tablePk = SLS_String::substrAfterFirstDelimiter($tablePk, '_');

					$this->_generic->useModel($tablePk, $dbPk, "user");
					$classFk = ucfirst($dbPk)."_".SLS_String::tableToClass($tablePk);
					$objectFk = new $classFk();
					$columns = array();
					$columnsLabel = array();
					$clause = array();
					$render = array();

					$columnTable = $objectFk->getTable();

					# add join
					# target
					$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
					${slsGraphQueryJoin.$i} = new $className();
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableTarget($slsGraphQueryTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasTarget($slsGraphQueryTableAlias);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnTarget($column);
					# /target

					# source
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableSource($columnTable);
					$slsGraphQueryJoin = $this->getTableAlias($columnTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasSource($slsGraphQueryJoin);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnSource($objectFk->getPrimaryKey());
					# /source

					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinMode('left');
					$i++;
					# /add join

					foreach($objectFk->getParams() as $key => $value)
					{
						array_push($columns,"`".$key."`");
						if (SLS_String::contains($labelPk,$key))
							$columnsLabel[$key] = strpos($labelPk,$key);
					}
					array_multisort($columnsLabel);

					foreach($columnsLabel as $columnLabel => $offset)
						array_push($clause,$columnLabel);

					$pattern = str_replace("'","''",$labelPk);
					foreach($clause as $columnC)
						$pattern = str_replace($columnC,"',"."CAST(".$slsGraphQueryJoin.".`".$columnC."` AS CHAR),'",$pattern);

					$columnConcat = "CONCAT('".$pattern."')";
				}

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($columnConcat);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_line');
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				$j++;
				# query column line

				# query column column
				$tmp = explode('.', $slsGraphData['sls_graph_pivot_column']);
				$column = $tmp[1];
				$columnConcat = $column;

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($column);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_column_id');
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				$j++;

				$columnFk = array_shift($xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($slsGraphQueryDbAlias.'_'.$slsGraphQueryTable)."' and @columnFk = '".$column."']",array("tablePk","labelPk")));
				if(!empty($columnFk))
				{
					$tablePk = $columnFk['attributes'][0]['value'];
					$labelPk = $columnFk['attributes'][1]['value'];

					$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tablePk, '_');
					$tablePk = SLS_String::substrAfterFirstDelimiter($tablePk, '_');

					$this->_generic->useModel($tablePk, $dbPk, "user");
					$classFk = ucfirst($dbPk)."_".SLS_String::tableToClass($tablePk);
					$objectFk = new $classFk();
					$columns = array();
					$columnsLabel = array();
					$clause = array();
					$render = array();

					$columnTable = $objectFk->getTable();

					# add join
					# target
					$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
					${slsGraphQueryJoin.$i} = new $className();
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableTarget($slsGraphQueryTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasTarget($slsGraphQueryTableAlias);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnTarget($column);
					# /target

					# source
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableSource($columnTable);
					$slsGraphQueryJoin = $this->getTableAlias($columnTable);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinTableAliasSource($slsGraphQueryJoin);
					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinColumnSource($objectFk->getPrimaryKey());
					# /source

					${slsGraphQueryJoin.$i}->setSlsGraphQueryJoinMode('left');
					$i++;
					# /add join

					foreach($objectFk->getParams() as $key => $value)
					{
						array_push($columns,"`".$key."`");
						if (SLS_String::contains($labelPk,$key))
							$columnsLabel[$key] = strpos($labelPk,$key);
					}
					array_multisort($columnsLabel);

					foreach($columnsLabel as $columnLabel => $offset)
						array_push($clause,$columnLabel);

					$pattern = str_replace("'","''",$labelPk);
					foreach($clause as $columnC)
						$pattern = str_replace($columnC,"',"."CAST(".$slsGraphQueryJoin.".`".$columnC."` AS CHAR),'",$pattern);

					$columnConcat = "CONCAT('".$pattern."')";
				}

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
				${slsGraphQueryColumn.$j} = new $className();
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnName($columnConcat);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnAlias('legend_column');
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTable($slsGraphQueryTable);
				${slsGraphQueryColumn.$j}->setSlsGraphQueryColumnTableAlias($slsGraphQueryTableAlias);
				# query column column

				# /query columns

				# query groups
				$tmp = explode('.', $slsGraphData['sls_graph_pivot_line']);
				$columnLine = $tmp[1];

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup1 = new $className();
				if(!$slsGraphQueryGroup1->setSlsGraphQueryGroupColumn($columnLine))
					$errors['sls_graph_pivot_line'] = 'Champ Ligne invalide';

				$tmp = explode('.', $slsGraphData['sls_graph_pivot_column']);
				$columnColumn = $tmp[1];

				$className = ucfirst($this->defaultDb)."_Sls_graph_query_group";
				$slsGraphQueryGroup2 = new $className();
				if(!$slsGraphQueryGroup2->setSlsGraphQueryGroupColumn($columnColumn))
					$errors['sls_graph_pivot_column'] = 'Champ colonne invalide';
				# /query groups

				$joins = $this->getQueryJoin($slsGraphQueryTable, $slsGraphQueryTableAlias, array($slsGraphData['sls_graph_pivot_column']));
			}
			else if($slsGraphData['sls_graph_type'] == 'list')
			{
				# columns
				$i = 1;
				$j = 1;
				$joins = array();

				foreach($slsGraphQueryData['sls_graph_query_column'] as $col)
				{
					$column = $col['sls_graph_query_column_value'];
					$path = explode('|', $column);
					$nbJoins = count($path);

					$data = explode('.', $path[$nbJoins-1]);
					$table = $data[0];

					$column = $data[1];
					$joinBefore = null;

					# joins
					for($k = 0; $k<$nbJoins ; $k++)
					{
						if($k == 0)
						{
							$columns = $sql->showColumns($slsGraphQueryTable);
							$columnSource = array_shift(array_filter($columns, array($this,'filterPK')));
							$columnSourcePK = $columnSource->Field;

							$join = array(
								'sls_graph_query_join_table_source' => $slsGraphQueryTable,
								'sls_graph_query_join_column_source' => $columnSourcePK
							);
						}
						else
						{
							$dataSource = explode('.', $path[$k]);
							$dataTarget = explode('.', $path[$k-1]);

							$tableSource = $dataSource[0];
							$tableTarget = $dataTarget[0];
							$columnSource = $dataSource[1];
							$columnTarget = $dataTarget[1];

							$columns = $sql->showColumns($tableSource);
							$columnSourcePK = array_shift(array_filter($columns, array($this,'filterPK')))->Field;

							$columns = $sql->showColumns($tableTarget);
							$this->columnTarget = $columnTarget;
							$columnTargetComment = array_shift(array_filter($columns, array($this,'filterFieldTarget')))->Comment;

							$join = array(
								'sls_graph_query_join_table_target' => $tableTarget,
								'sls_graph_query_join_column_target' =>$columnTarget,
								'sls_graph_query_join_table_comment_target' => empty($columnTargetComment) ? $columnTarget : $columnTargetComment,
								'sls_graph_query_join_table_source' => $tableSource,
								'sls_graph_query_join_column_source' => $columnSourcePK
							);
						}

						$joinSearch = $this->array_search_multi($join, $joins);
						if(empty($joinSearch))
						{
							$join['sls_graph_query_join_table_alias_source'] = ($k == 0) ? $slsGraphQueryTableAlias : $this->getTableAlias($join['sls_graph_query_join_table_source']); /*$join['sls_graph_query_join_table_source'].$aliasIndex++*/;

							if($k > 0)
							{
								$className = ucfirst($this->defaultDb)."_Sls_graph_query_join";
								${slsGraphQueryJoin.$j} = new $className();
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinTableSource($join['sls_graph_query_join_table_source']);
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinTableAliasSource($join['sls_graph_query_join_table_alias_source']);
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinColumnSource($join['sls_graph_query_join_column_source']);
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinTableTarget($join['sls_graph_query_join_table_target']);
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinTableAliasTarget($joinBefore['sls_graph_query_join_table_alias_source']);
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinColumnTarget($join['sls_graph_query_join_column_target']);
								${slsGraphQueryJoin.$j}->setSlsGraphQueryJoinMode('left');
								$j++;
							}

							array_push($joins, $join);
						}
						else
							$join = $joinSearch;

						$joinBefore = $join;
					}
					# /joins

					$columns = $sql->showColumns($table);
					$this->column = $column;
					$columnComment = array_shift(array_filter($columns, array($this,'filterColumnField')))->Comment;
										
					$tableComment = $join['sls_graph_query_join_table_comment_target'];
					$this->table = $table;
					if(empty($tableComment))
					{
						$tables = $sql->showTables();
						$tableComment = array_shift(array_filter($tables, array($this,'filterTable4')))->Comment;
					}
					
					$className = ucfirst($this->defaultDb)."_Sls_graph_query_column";
					${slsGraphQueryColumn.$i} = new $className();
					${slsGraphQueryColumn.$i}->setSlsGraphQueryColumnTable($join['sls_graph_query_join_table_source']);
					${slsGraphQueryColumn.$i}->setSlsGraphQueryColumnTableAlias($join['sls_graph_query_join_table_alias_source']);
					${slsGraphQueryColumn.$i}->setSlsGraphQueryColumnName($column);
					${slsGraphQueryColumn.$i}->setSlsGraphQueryColumnAlias($tableComment.' - '.$columnComment);
					$i++;
				}

				# /columns
			}

			# query where
			if(!empty($slsGraphQueryData['sls_graph_query_where']))
			{
				$i = 0; $j = 0;
				$this->iterateSetSlsGraphQueryWhere($slsGraphQueryData['sls_graph_query_where'], $i, $j, $slsGraphQueryWheres, $errors, $joins);
			}
			# /query where

			if (empty($errors))
			{
				$sql->changeDb($this->defaultDb);

				# query
				$slsGraphQuery->create();
				# /query

				# graph
				$slsGraph->setSlsGraphDateAdd(date('Y-m-d H:i:s'));
				$slsGraph->setSlsGraphQueryId($slsGraphQuery->sls_graph_query_id);
				$slsGraph->create();
				# /graph

				# query joins
				$i = 1;
				//$queryJoin1
				while(${slsGraphQueryJoin.$i})
				{
					${slsGraphQueryJoin.$i}->setSlsGraphQueryId($slsGraphQuery->sls_graph_query_id);
					${slsGraphQueryJoin.$i}->create();
					$i++;
				}
				# /query joins

				# query columns
				$i = 1;
				while(${slsGraphQueryColumn.$i})
				{
					${slsGraphQueryColumn.$i}->setSlsGraphQueryId($slsGraphQuery->sls_graph_query_id);
					${slsGraphQueryColumn.$i}->create();
					$i++;
				}
				# /query columns

				# query groups
				$i = 1;
				while(${slsGraphQueryGroup.$i})
				{
					${slsGraphQueryGroup.$i}->setSlsGraphQueryGroupTable($slsGraphQueryTable);
					${slsGraphQueryGroup.$i}->setSlsGraphQueryGroupTableAlias($slsGraphQueryTableAlias);
					${slsGraphQueryGroup.$i}->setSlsGraphQueryId($slsGraphQuery->sls_graph_query_id);
					${slsGraphQueryGroup.$i}->create();
					$i++;
				}
				# /query groups

				# query where
				if(!empty($slsGraphQueryData['sls_graph_query_where']))
				{
					$i = 0;
					$this->iterateCreateQueryWhere($slsGraphQueryData['sls_graph_query_where'], 0, $i, $slsGraphQueryWheres, $slsGraphQuery->sls_graph_query_id);
				}
				# /query where

				$this->forward('SLS_Bo', 'ReportingBo');
			}
			else
			{
				$xml->startTag("errors");
				foreach($errors as $key => $error)
				{
					if($key == 'sls_graph_query_where')
					{
						foreach($error as $queryWhereIndex => $queryWhereErrors)
						{
							foreach($queryWhereErrors as $queryWhereKey => $queryWhereError)
							{
								$xml->addFullTag("error", $queryWhereError, true, array("num" => $queryWhereIndex, "column" => $queryWhereKey));
							}
						}
					}
					else
						$xml->addFullTag("error", $error, true, array("column" => $key));
				}

				$xml->endTag("errors");
			}

			$slsGraphQueryData = $this->_http->getParam('sls_graph_query');
		}
		# /reload

		# graph
		$xml->startTag('sls_graph');
			foreach($slsGraphData as $key => $value)
				$xml->addFullTag($key, $value, true);
			$xml->addFullTag('graph_table_fields_class', empty($tableFields) ? 'hide' : '', true);
			$xml->startTag('sls_graph_query');
			foreach($slsGraphQueryData as $key => $value)
			{
				if($key == 'sls_graph_query_where')
				{
					$i = 0; $j = 0;
					$this->iterateAddXmlQueryWhere($value, $i, $j, $xml);
				}
				else if($key == 'sls_graph_query_column')
				{
					$xml->startTag('sls_graph_query_columns');
					foreach($value as $column)
					{
						$xml->startTag('sls_graph_query_column');
						$xml->addFullTag('sls_graph_query_column_value', $column['sls_graph_query_column_value'], true);
						$xml->addFullTag('sls_graph_query_column_label', $column['sls_graph_query_column_label'], true);
						$xml->endTag('sls_graph_query_column');
					}
					$xml->endTag('sls_graph_query_columns');
				}
				else
					$xml->addFullTag($key, $value, true);
			}

			$xml->endTag('sls_graph_query');
		$xml->endTag('sls_graph');
		# /graph

		$labels = array(
			'SLS_GRAPH_TYPE_PIE' => "Pie Chart",
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
			'SLS_QUERY_OPERATOR_NOTNULL' => "IS NOT NULL"
		);

		# graph types
		$xml->startTag('sls_graph_types');
		foreach($slsGraphTypes as $slsGraphType){
			$xml->startTag('sls_graph_type');
			$xml->addFullTag('sls_graph_type_value', $slsGraphType, true);
			$xml->addFullTag('sls_graph_type_label', $labels['SLS_GRAPH_TYPE_'.mb_strtoupper($slsGraphType, 'UTF-8')], true);
			$xml->endTag('sls_graph_type');
		}
		$xml->endTag('sls_graph_types');
		# /graph types

		# aggregation types
		$xml->startTag('sls_graph_aggregation_types');
		foreach($slsGraphAggregationTypes as $slsGraphAggregationType){
			$xml->startTag('sls_graph_aggregation_type');
			$xml->addFullTag('sls_graph_aggregation_type_value', $slsGraphAggregationType, true);
			$xml->addFullTag('sls_graph_aggregation_type_label', $labels['SLS_AGGREGATION_TYPE_'.mb_strtoupper($slsGraphAggregationType, 'UTF-8')], true);
			$xml->endTag('sls_graph_aggregation_type');
		}
		$xml->endTag('sls_graph_aggregation_types');
		# /aggregation types

		# query operators
		$xml->startTag('sls_graph_query_operators');
		foreach($slsGraphQueryOperators as $slsGraphQueryOperator){
			$xml->startTag('sls_graph_query_operator');
			$xml->addFullTag('sls_graph_query_operator_value', $slsGraphQueryOperator, true);
			$xml->addFullTag('sls_graph_query_operator_label', $labels['SLS_QUERY_OPERATOR_'.mb_strtoupper($slsGraphQueryOperator, 'UTF-8')], true);
			$xml->endTag('sls_graph_query_operator');
		}
		$xml->endTag('sls_graph_query_operators');
		# /query operators

		# tables
		$xml->startTag('tables');
		$dbs = $sql->getDbs();
		foreach($dbs as $db)
		{
			$sql->changeDb($db);
			$tables = $sql->showTables();
			usort($tables, array($this,'cmpTables'));

			foreach($tables as $table)
			{
				$xml->startTag('table');
				$xml->addFullTag('table_name', $db.'.'.$table->Name, true);
				$xml->addFullTag('table_label', $db.' - '.$table->Name, true);
				$xml->endTag('table');
			}
		}
		$xml->endTag('tables');
		# /tables

		$xml->addFullTag("url_reporting_getfields",$this->_generic->getFullPath("SLS_Bo","ReportingBoGetFields"),true);
		$xml->addFullTag("url_reporting_getfieldsfrommutipletables",$this->_generic->getFullPath("SLS_Bo","ReportingBoGetFieldsFromMultipleTables"),true);

		$this->saveXML($xml);
	}
	
	public function filterField($e)
	{
		return $e->Field;
	}
	
	public function filterColumnField($e)
	{
		return $e->Field == $this->column;
	}
	
	public function filterFieldTarget($e)
	{
		return $e->Field == $this->columnTarget;
	}
	
	public function filterTable4($e)
	{
		return $e->Name == $this->table;
	}
}
?>