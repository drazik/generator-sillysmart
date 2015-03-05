<?php
/**
* Class ReportingBoGetFieldsFromMultipleTables into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBoGetFieldsFromMultipleTables extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		
		/* Vars */
		$json = array();
		$this->xml = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig('configSls').'fks.xml'));

		// get all different tables.f$slsGraphQueryTable
		$tableName = $this->_http->getParam('table_name');
		$tmp = explode('.', $tableName);

		if(count($tmp) != 2)
			die;

		$slsGraphQueryDbAlias = $tmp[0];
		$slsGraphQueryTable = $tmp[1];
				
		$this->sql->changeDb($slsGraphQueryDbAlias);
		
		$slsGraphQueryTableAlias = $this->getTableAlias($slsGraphQueryTable);
		$slsGraphQueryColumns = $this->_http->getParam('columns');

		# columns
		$joins = array();

		if(!empty($slsGraphQueryColumns))		
		{
			foreach($slsGraphQueryColumns as $slsGraphQueryColumn)
			{
				$path = explode('|', $slsGraphQueryColumn);
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
						$columns = $this->sql->showColumns($slsGraphQueryTable);
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
						
						$columns = $this->sql->showColumns($tableSource);

						$columnSourcePK = array_shift(array_filter($columns, array($this,'filterPK')))->Field;

						$join = array(
							'sls_graph_query_join_table_target' => $tableTarget,
							'sls_graph_query_join_column_target' =>$columnTarget,
							'sls_graph_query_join_table_source' => $tableSource,
							'sls_graph_query_join_column_source' => $columnSourcePK
						);
					}

					$joinSearch = $this->array_search_multi($join, $joins);
					if(empty($joinSearch))
					{
						$join['sls_graph_query_join_table_alias_source'] = ($k == 0) ? $slsGraphQueryTableAlias : $this->getTableAlias($join['sls_graph_query_join_table_source']);
						$join['sls_graph_query_join_table_alias_target'] = ($k == 0) ? '' : $joinBefore['sls_graph_query_join_table_alias_source'];
						array_push($joins, $join);
					}
					else
						$join = $joinSearch;

					$joinBefore = $join;
				}
				# /joins
			}
		}

		if ($this->sql->tableExists($slsGraphQueryTable))
		{
			$json['status'] = 'OK';
			$json['fields'] = array();

			$this->iterateAddFieldsTable($json['fields'], $joins, $slsGraphQueryTable, $slsGraphQueryTableAlias, '');
		}
		else
		{
			$json['status'] = 'ERROR';
			$json['error'] = "Table doesn't exist";
		}

		echo json_encode($json);
		die;
	}

	public function iterateAddFieldsTable(&$results, $joins, $table, $tableAlias, $comment)
	{
		// get Columns from table
		$fields = $this->sql->showColumns($table);
		//usort($fields, 'cmpFields');

		// get Attributes -> needed to know isFk
		$getAttributes = $this->xml->getTagsAttributes('//sls_configs/entry[@tableFk="'.$this->defaultDb.'_'.$table.'"]', array('columnFk'));

		// Format Attributes
		$columnFks = array();
		for ($i = 0; $i < $count = count($getAttributes); $i++)
			array_push($columnFks, $getAttributes[$i]['attributes'][0]['value']);

		$comment .= (!empty($comment) ? ' / ' : '');

		foreach ($fields as $field)
		{
			array_push($results, array(
					'field_name' => $tableAlias.'.'.$field->Field,
					'field_label' => $comment.$field->Field)
			);
			$fieldName = $field->Field;
			$this->fieldName = $fieldName;
			$this->tableAlias = $tableAlias;
			$join = array_shift(array_filter($joins, array($this,'filterField')));

			if (in_array($field->Field, $columnFks) && !empty($join))
			{
				$this->iterateAddFieldsTable($results, $joins, $join['sls_graph_query_join_table_source'], $join['sls_graph_query_join_table_alias_source'], $comment.$field->Field);
			}
		}
	}
	
	public function filterField($e)
	{
		return $e['sls_graph_query_join_column_target'] == $this->fieldName && $e['sls_graph_query_join_table_alias_target'] == $this->tableAlias;
	}
}
?>