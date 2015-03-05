<?php
class SLS_BoEditBo extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);

		# Params
		$tableName = $this->_http->getParam('name');
		$this->_db_alias = SLS_String::substrBeforeFirstDelimiter($tableName, "_");
		$this->_table = SLS_String::substrAfterFirstDelimiter($tableName, "_");
		# /Params

		# Objects
		$errors = array();
		$operators = array('like','notlike','startwith','endwith','equal','notequal','in','notin','lt','lte','gt','gte','null','notnull');
		$operatorsNeedValue = array('like','notlike','startwith','endwith','equal','notequal','in','notin','lt','lte','gt','gte');
		$orders = array( 'desc', 'asc');
		$limits = array( '20', '50', '100', '250', '500', '1000');

		$this->_db = new SLS_Sql();
		$this->_db->changeDb($this->_db_alias);
		if(!$this->_db->tableExists($this->_table))
			$this->forward('SLS_Default', 'UrlError');

		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($this->_db_alias)), "user");
		$this->_object = new $className();

		$this->_xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
		$this->_xmlType = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml"));
		$this->_xmlBearers = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bearers.xml"));
		
		$this->_xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($className)."']";
		$boExists = $this->_xmlBo->getTag($boPath."/@type");
		if (empty($boExists))
			$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($className)."']";
		# /Objects
		
		$menuCategories = $this->_xmlBo->getTags("//sls_configs/entry[@type='category']/@name");
		$xml->startTag("categories");
		for($i=0 ; $i<$count=count($menuCategories) ; $i++)
			$xml->addFullTag("category",$menuCategories[$i],true);
		$xml->endTag("categories");
			
		$tableAttributes = array_shift($this->_xmlBo->getTagsAttributes($boPath, array('multilanguage')));

		# reload
		if($this->_http->getParam('reload') == 'true')
		{
			$boData = $this->_http->getParam('bo');
			$newCategory = $this->_http->getParam('category');
			$results = $this->_xmlBo->getTagsAttributes($boPath.'/joins/join', array('table', 'column'));

			$joinsNews = !empty($boData['joins']) ? $boData['joins'] : array();
			$joinsOld = array();
			foreach($results as $result)
				array_push($joinsOld, SLS_String::substrAfterFirstDelimiter($result['attributes'][0]['value'], '_'));

			$joinsToDelete = array_diff($joinsOld, $joinsNews);
			$joinsToAdd = array_diff($joinsNews, $joinsOld);

			$xmlNew = '';

			# columns

			# add columns of news join tables
			if(!empty($joinsToAdd))
			{
				foreach($joinsToAdd as $join)
				{
					$tableColumns = $this->_db->showColumns($join);
					if(!empty($tableColumns))
					{
						foreach($tableColumns as $tableColumn)
						{
							if(!in_array($tableColumn, array('pk_lang')))
							{
								array_push($boData['columns'], array(
									'table' => $join,
									'column_value' => $join.'.'.$tableColumn->Field,
									'column_label' => $join.' / '.$tableColumn->Field,
									'display_filter' => 'on',
									'display_list' => 'off',
									'allow_edit' => 'off',
									'allow_html' => 'off',
									'multilanguage' => 'off',
								));
							}
						}
					}
				}
			}

			$xmlNew .= '<columns>';
			if(!empty($boData['columns']))
			{
				foreach($boData['columns'] as $index => $column)
				{
					$table = $this->_db_alias.'_'.$column['table'];
					$name = SLS_String::substrAfterLastDelimiter($column['column_value'], '.');
					$multilanguage = $column['multilanguage'] == 'on' ? 'true' : 'false';
					$displayFilter = $column['display_filter'] == 'on' ? 'true' : 'false';
					$displayList = $column['display_list'] == 'on' ? 'true' : 'false';
					$allowEdit = $column['allow_edit'] == 'on' ? 'true' : 'false';
					$allowHtml = $column['allow_html'] == 'on' ? 'true' : 'false';

					if($table == $tableName || (is_array($joinsNews) && in_array($column['table'], $joinsNews)))
						$xmlNew .= '<column table="'.$table.'" name="'.$name.'" multilanguage="'.$multilanguage.'" displayFilter="'.$displayFilter.'" displayList="'.$displayList.'" allowEdit="'.$allowEdit.'" allowHtml="'.$allowHtml.'" />';
					else
						unset($boData['columns'][$index]);
				}
			}

			$xmlNew .= '</columns>';
			# /columns

			# joins
			$xmlNew .= '<joins>';
			if(!empty($boData['joins']))
			{
				foreach($boData['joins'] as $index => $join)
				{

					$tablePk = $this->_db_alias.'_'.ucfirst($join);
					$res = array_shift($this->_xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$tableName."' and @tablePk='".$tablePk."']",array("columnFk")));
					$table = $this->_db_alias.'_'.$join;
					$column = $res['attributes'][0]['value'];


					if($table == $tableName || (is_array($joinsNews) && in_array($join, $joinsNews)))
						$xmlNew .= '<join table="'.$table.'" column="'.$column.'" />';
					else
						unset($boData['joins'][$index]);
				}
			}
			$xmlNew .= '</joins>';
			# /joins

			# wheres
			$xmlNew .= '<wheres>';
			if(!empty($boData['wheres']))
			{
				foreach($boData['wheres'] as $index => $where)
				{
					$table = $this->_db_alias.'_'.SLS_String::substrBeforeFirstDelimiter($where['column'], '.');
					$column = SLS_String::substrAfterFirstDelimiter($where['column'], '.');
					$value = in_array($where['mode'], $operatorsNeedValue) ? $where['value'] : '';
					$mode = $where['mode'];

					if($table == $tableName || (is_array($joinsNews) && in_array(SLS_String::substrBeforeFirstDelimiter($where['column'], '.'), $joinsNews)))
						$xmlNew .= '<where table="'.$table.'" column="'.$column.'" value="'.$value.'" mode="'.$mode.'" />';
					else
						unset($boData['wheres'][$index]);
				}
			}
			$xmlNew .= '</wheres>';
			# /wheres

			# groups
			$xmlNew .= '<groups>';
			if(!empty($boData['groups']))
			{
				foreach($boData['groups'] as $index => $group)
				{
					$table = $this->_db_alias.'_'.SLS_String::substrBeforeFirstDelimiter($group, '.');
					$column = SLS_String::substrAfterFirstDelimiter($group, '.');

					if($table == $tableName || (is_array($joinsNews) && in_array(SLS_String::substrBeforeFirstDelimiter($group, '.'), $joinsNews)))
						$xmlNew .= '<group table="'.$table.'" column="'.$column.'" />';
					else
						unset($boData['groups'][$index]);
				}
			}
			$xmlNew .= '</groups>';
			# /groups

			# orders
			$xmlNew .= '<orders>';
			if(!empty($boData['orders']))
			{
				foreach($boData['orders'] as $index => $order)
				{
					$table = $this->_db_alias.'_'.SLS_String::substrBeforeFirstDelimiter($order['column'], '.');
					$column = SLS_String::substrAfterFirstDelimiter($order['column'], '.');
					$orderValue = $order['order'];

					if($table == $tableName || (is_array($joinsNews) && in_array(SLS_String::substrBeforeFirstDelimiter($order['column'], '.'), $joinsNews)))
						$xmlNew .= '<order table="'.$table.'" column="'.$column.'" order="'.$orderValue.'" />';
					else
						unset($boData['orders'][$index]);
				}
			}
			$xmlNew .= '</orders>';
			# /orders

			# limits
			$xmlNew .= '<limits>';
			if(!empty($boData['limits']))
			{
				foreach($boData['limits'] as $index => $limit)
				{
					$length = $limit['length'];
					$xmlNew .= '<limit start="0" length="'.$length.'" />';
				}
			}
			$xmlNew .= '</limits>';
			# limits


			# children
			$xmlNew .= '<children>';

			if(!empty($boData['children']))
			{
				foreach($boData['children'] as $index => $child)
				{
					$this->_generic->useModel($child, $this->_db_alias, 'user');
					try
					{
						$className = ucfirst($this->_db_alias.'_'.ucfirst($child));
						$classObject = new $className();

						$column = $classObject->getPrimaryKey();
						$table = strtolower($className);
						$xmlNew .= '<child table="'.$table.'" column="'.$column.'" />';
					}
					catch (Exception $e){}
				}
			}
			$xmlNew .= '</children>';
			# /children
			
			$newPath = (empty($newCategory)) ? '//sls_configs' : '//sls_configs/entry[@type="category" and @name="'.$newCategory.'"]';
			$this->_xmlBo->deleteTags($boPath);
			$this->_xmlBo->appendXMLNode($newPath, '<entry type="table" name="'.strtolower($this->_db_alias.'_'.$this->_table).'" multilanguage="'.($this->_object->isMultilanguage() ? 'true' : 'false').'">'.$xmlNew.'</entry>');
			$this->_xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml");
			$this->_xmlBo->refresh();
			
			// Crappy hack to force reload
			$this->_generic->forward("SLS_Bo","EditBo",array("name"=>$this->_http->getParam('name')));
		}
		# /reload
		else
		{
			$tempData = array(
				'columns' => $this->_xmlBo->getTagsAttributes($boPath."/columns/column",array("table","name","multilanguage","displayFilter", "displayList", "allowEdit", "allowHtml")),
				'joins' => $this->_xmlBo->getTagsAttributes($boPath."/joins/join",array("table")),
				'wheres' => $this->_xmlBo->getTagsAttributes($boPath."/wheres/where",array("table","column","value","mode")),
				'groups' => $this->_xmlBo->getTagsAttributes($boPath."/groups/group",array("table","column")),
				'orders' => $this->_xmlBo->getTagsAttributes($boPath."/orders/order",array("table","column","order")),
				'limits' => $this->_xmlBo->getTagsAttributes($boPath."/limits/limit",array("start","length")),
				'children' => $this->_xmlBo->getTagsAttributes($boPath."/children/child",array("table","column"))
			);

			# columns
			$boData['columns'] = array();
			$position = 1;
			$strings = array();
			foreach($tempData['columns'] as $column)
			{
				$table = SLS_String::substrAfterFirstDelimiter($column['attributes'][0]['value'], '_');
				
				// Avoid pk
				$class = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($table);
				$this->_generic->useModel(SLS_String::tableToClass($table),$this->_db_alias,"user");
				$object = new $class();
				
				// String type ?
				if (!array_key_exists($table,$strings))
				{
					$columns = $this->_db->showColumns($table);
					for($i=0 ; $i<$count=count($columns) ; $i++)
					{
						$strings[$table][$columns[$i]->Field] = (SLS_String::contains($columns[$i]->Type,"text") || SLS_String::contains($columns[$i]->Type,"char")) ? true : false;
					}
				}
				
				// Avoid fk
				$isFk = $this->_xmlFk->getTags("//sls_configs/entry[@tableFk='".strtolower($this->_db_alias."_".$table)."' and @columnFk='".$column['attributes'][1]['value']."']/@tablePk");
				
				// Avoid quick edit on type file
				$specificTypeFileExists = $this->_xmlType->getTag("//sls_configs/entry[@table='".$column['attributes'][0]['value']."' and @column='".$column['attributes'][1]['value']."' and (@type='file_all' or @type='file_img')]/@column");
				
				array_push($boData['columns'], array(
					'table' => $table,
					'column_value' => $table.'.'.$column['attributes'][1]['value'],
					'column_label' => $table.' / '.$column['attributes'][1]['value'],
					'display_filter' => ($column['attributes'][3]['value'] == 'true') ? 'on' : 'off',
					'display_list' => ($column['attributes'][4]['value'] == 'true') ? 'on' : 'off',
					'allow_edit' => ($column['attributes'][5]['value'] == 'true') ? 'on' : 'off',
					'allow_html' => ($column['attributes'][6]['value'] == 'true') ? 'on' : 'off',
					'multilanguage' => ($column['attributes'][2]['value'] == 'true') ? 'on' : 'off',
					'type_file' => (!empty($specificTypeFileExists)) ? "true" : "false",
					'type_pk' => ($column['attributes'][1]['value'] == $object->getPrimaryKey() || $column['attributes'][1]['value'] == "pk_lang") ? "true" : "false",
					'type_fk' => ($isFk) ? "true" : "false",
					'type_string' => ($strings[$table][$column['attributes'][1]['value']]) ? "true" : "false"
				));
				$position++;
			}
			# /columns

			# joins
			$boData['joins'] = array();
			foreach($tempData['joins'] as $join)
				array_push($boData['joins'], SLS_String::substrAfterFirstDelimiter($join['attributes'][0]['value'], '_'));
			# /joins

			# wheres
			$boData['wheres'] = array();
			foreach($tempData['wheres'] as $where)
			{
				array_push($boData['wheres'], array(
					'column' => SLS_String::substrAfterFirstDelimiter($where['attributes'][0]['value'], '_').'.'.$where['attributes'][1]['value'],
					'mode' => $where['attributes'][3]['value'],
					'value' => $where['attributes'][2]['value']
				));
			}
			# /wheres

			# groups
			$boData['groups'] = array();
			foreach($tempData['groups'] as $group)
				array_push($boData['groups'], SLS_String::substrAfterFirstDelimiter($group['attributes'][0]['value'], '_').'.'.$group['attributes'][1]['value']);
			# /groups

			# orders
			$boData['orders'] = array();
			foreach($tempData['orders'] as $order)
			{
				array_push($boData['orders'], array(
					'column' =>  SLS_String::substrAfterFirstDelimiter($order['attributes'][0]['value'], '_').'.'.$order['attributes'][1]['value'],
					'order' => $order['attributes'][2]['value']
				));
			}
			# /orders

			# limits
			$boData['limits'] = array();
			foreach($tempData['limits'] as $limit)
			{
				array_push($boData['limits'], array(
					'length' =>  $limit['attributes'][1]['value']
				));
			}
			# /limits

			# children
			$boData['children'] = array();
			foreach($tempData['children'] as $child)
				array_push($boData['children'], SLS_String::substrAfterFirstDelimiter($child['attributes'][0]['value'], '_'));
			# /children
		}

		$xml->addFullTag("delete",$this->_generic->getFullPath("SLS_Bo","DeleteBo",array(),false));
		$menuCategoryExist = $this->_xmlBo->getTag("//sls_configs/entry[@type='category' and entry[@type='table' and @name='".strtolower($className)."']]/@name");
		$xml->startTag('bo');
			$xml->addFullTag('table', $this->_table, true);
			$xml->addFullTag('db_alias', $this->_db_alias, true);
			$xml->addFullTag('category', $menuCategoryExist, true);
			$xml->addFullTag('multilanguage', $tableAttributes['attributes'][0]['value'], true);
			$xml->addFullTag('class', $className, true);

			if(!empty($boData))
			{
				foreach($boData as $key => $values)
				{
					$xml->startTag($key);
					if(!empty($values) && is_array($values))
					{
						foreach($values as $value)
						{
							if(is_array($value))
							{
								$xml->startTag('line');
								foreach($value as $col => $val)
									$xml->addFullTag($col, $val, true);

								if($key == 'columns' && is_array($boData['groups']) && in_array($value['column_value'] , $boData['groups']))
									$xml->addFullTag('column_group', 'true', true);

								$xml->endTag('line');
							}
							else
								$xml->addFullTag('line', $value, true);
						}

					}
					$xml->endTag($key);
				}
			}
		$xml->endTag('bo');

		$children = $this->_xmlFk->getTagsAttributes("//sls_configs/entry[@tablePk='".(SLS_String::substrBeforeFirstDelimiter($tableName, '_').'_'.ucfirst(SLS_String::substrAfterFirstDelimiter($tableName, '_')))."']",array("tableFk", 'columnFk'));
		$xml->startTag('children');
		$childrenFound = array();
		if(!empty($children))
		{
			foreach($children as $child)
			{
				$bearerExists = $this->_xmlBearers->getTag("//sls_configs/entry[@tableBearer='".ucfirst(strtolower(SLS_String::substrBeforeFirstDelimiter($child['attributes'][0]['value'],"_")))."_".SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($child['attributes'][0]['value'],"_"))."']/@tableBearer");
				if (empty($bearerExists) && !in_array($child['attributes'][0]['value'],$childrenFound))
				{
					$xml->startTag('child');
						$tmp = SLS_String::substrAfterFirstDelimiter($child['attributes'][0]['value'], '_');
						$xml->addFullTag('child_selected', is_array($boData['children']) && in_array($tmp, $boData['children']) ? 'true' : 'false', true);
						$xml->addFullTag('child_value', $tmp, true);
					$xml->endTag('child');
					$childrenFound[] = $child['attributes'][0]['value'];
				}
			}
		}
		$xml->endTag('children');

		$joins = $this->_xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$tableName."']",array("tablePk", "columnFk"));
		$xml->startTag('joins');
		if(!empty($joins))
		{
			foreach($joins as $join)
			{
				$tableTmp = $join['attributes'][0]['value'];
				$tableLowerTmp = strtolower($join['attributes'][0]['value']);
				$tableNameLowerTmp = SLS_String::substrAfterFirstDelimiter($tableLowerTmp, '_');
				$columnTmp = $join['attributes'][1]['value'];

				$classNameTmp = ucfirst($tableTmp);
				$this->_generic->useModel($tableNameLowerTmp, $this->_db_alias, "user");
				$classObject = new $classNameTmp();

				if($classObject->getPrimaryKey() == $columnTmp)
				{
					$xml->addFullTag('join', $tableNameLowerTmp, true);
				}
			}
		}
		$xml->endTag('joins');

		$labels = array(
			'OPERATOR_LIKE' => "LIKE",
			'OPERATOR_NOTLIKE' => "NOT LIKE",
			'OPERATOR_STARTWITH' => "START WITH",
			'OPERATOR_ENDWITH' => "END WITH",
			'OPERATOR_EQUAL' => "EQUAL",
			'OPERATOR_NOTEQUAL' => "NOT EQUAL",
			'OPERATOR_IN' => "IN",
			'OPERATOR_NOTIN' => "NOT IN",
			'OPERATOR_LT' => "LESS THAN",
			'OPERATOR_LTE' => "LESS THAN EQUAL",
			'OPERATOR_GT' => "GREATER THAN",
			'OPERATOR_GTE' => "GREATER THAN EQUAL",
			'OPERATOR_NULL' => "IS NULL",
			'OPERATOR_NOTNULL' => "IS NOT NULL",
			'ORDER_ASC' => 'ASC',
			'ORDER_DESC' => 'DESC'
		);
		
		# operators
		$xml->startTag('operators');
		if(!empty($operators))
		{
			foreach($operators as $operator)
			{
				$xml->startTag('operator');
				$xml->addFullTag('operator_need_value', (is_array($operatorsNeedValue) && in_array($operator, $operatorsNeedValue)) ? 'true' : 'false', true);
				$xml->addFullTag('operator_value', $operator, true);
				$xml->addFullTag('operator_label', $labels['OPERATOR_'.mb_strtoupper($operator, 'UTF-8')], true);
				$xml->endTag('operator');
			}
		}
		$xml->endTag('operators');
		# /operators

		# orders
		$xml->startTag('orders');
		if(!empty($orders))
		{
			foreach($orders as $order)
			{
				$xml->startTag('order');
				$xml->addFullTag('order_value', $order, true);
				$xml->addFullTag('order_label', $labels['ORDER_'.mb_strtoupper($order, 'UTF-8')], true);
				$xml->endTag('order');
			}
		}
		$xml->endTag('orders');
		# /orders

		# limits
		$xml->startTag('limits');
		if(!empty($limits))
		{
			foreach($limits as $limit)
				$xml->addFullTag('limit', $limit, true);
		}
		$xml->endTag('limits');
		# /limits

		$xml->addFullTag("url_add_category",$this->_generic->getFullPath("SLS_Bo","AddBoCategory",array("name" => $this->_http->getParam("name"))),true);
		$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","DeleteBo",array("name" => $this->_db_alias."_".$this->_table)),true);

		$this->saveXML($xml);
	}
}
?>