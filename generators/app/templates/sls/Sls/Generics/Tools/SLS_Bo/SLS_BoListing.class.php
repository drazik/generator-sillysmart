<?php
/**
 * SLS_BoListing Tool - Generate back-office listing of database entities with pagination, order, number results, filters features
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package SLS.Generics.Tools.SLS_Bo
 * @since 1.1
 */
class SLS_BoListing extends __SLS_Bo
{
	public $_xml = null;
	public $_db_alias = null;
	public $_table = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct($xml,$db,$table)
	{
		parent::__construct();
		
		$this->_xml = $xml;
		$this->_db_alias = $db;
		$this->_table = $table;
	}
	
	public function getXML()
	{
		# Objects
		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($this->_db_alias)),"user");
		$this->_object = new $className();
		$this->_table = $this->_object->getTable();
		$this->_gap = 0;
		$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($className)."']";
		$boExists = $this->_xmlBo->getTag($boPath."/@type");
		if (empty($boExists))		
			$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($className)."']";
		# /Objects
		
		# User params
		$this->_join = array();
		$this->_where = array();
		$this->_group = array();
		$this->_order = array();
		$this->_limit = array();
		$this->_reload = ($this->_http->getParam("reload-filters") == "true") ? true : false;
		$joins = $this->_xmlBo->getTagsAttributes($boPath."/joins/join",array("table","column"));
		$wheres = $this->_xmlBo->getTagsAttributes($boPath."/wheres/where",array("table","column","value","mode"));
		$groups = $this->_xmlBo->getTagsAttributes($boPath."/groups/group",array("table","column"));
		$orders = $this->_xmlBo->getTagsAttributes($boPath."/orders/order",array("table","column","order"));
		$limits = array_shift($this->_xmlBo->getTagsAttributes($boPath."/limits/limit",array("start","length")));
		if (!empty($joins))
			for($i=0 ; $i<$count=count($joins) ; $i++)
				$this->_join[] = array("table" => SLS_String::substrAfterFirstDelimiter($joins[$i]["attributes"][0]["value"],"_"), "column" => $joins[$i]["attributes"][1]["value"], "mode" => "left");
		if (!empty($wheres) && !$this->_reload)		
			for($i=0 ; $i<$count=count($wheres) ; $i++)
				$this->_where[] = array("column" => SLS_String::substrAfterFirstDelimiter($wheres[$i]["attributes"][0]["value"],"_").".".$wheres[$i]["attributes"][1]["value"], "value" => $wheres[$i]["attributes"][2]["value"], "mode" => $wheres[$i]["attributes"][3]["value"]);
		if (!empty($groups))		
			for($i=0 ; $i<$count=count($groups) ; $i++)
				$this->_group[] = SLS_String::substrAfterFirstDelimiter($groups[$i]["attributes"][0]["value"],"_").".".$groups[$i]["attributes"][1]["value"];
		if (!empty($orders))		
			for($i=0 ; $i<$count=count($orders) ; $i++)
				$this->_order[] = array("column" => SLS_String::substrAfterFirstDelimiter($orders[$i]["attributes"][0]["value"],"_").".".$orders[$i]["attributes"][1]["value"], "order" => $orders[$i]["attributes"][2]["value"]);
		if (!empty($limits))
		{
			$this->_limit["start"] = $limits["attributes"][0]["value"];
			$this->_limit["length"] = $limits["attributes"][1]["value"];
		}		
		# /User params
		
		# Comments
		$this->_comments = array();
		$this->_types = array();
		$commentsTable = array();
		$tables = array($this->_table);
		foreach($this->_join as $joinTable)
			$tables[] = $joinTable["table"];
		foreach($tables as $commentTable)
		{
			if (!array_key_exists($commentTable,$commentsTable))
			{
				$comment = $this->_object->getTableComment($commentTable,$this->_db_alias);
				if (empty($comment))
					$comment = $commentTable;
				if (SLS_String::startsWith($comment,"sls:lang:"))
				{
					$key = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
					$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $commentTable : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
				}
				$commentsTable[$commentTable] = $comment;
			}
		}		
		# /Comments
		
		# Columns
		$this->_columns = array();
		$this->_columns = array_merge($this->_columns, $this->getTableColumns($this->_db_alias,$this->_table,$boPath,"",true,true));
		foreach($this->_join as $joinTable)
			$this->_columns = array_merge($this->_columns, $this->getTableColumns($this->_db_alias,$joinTable["table"],$boPath,"",false,true));
		$this->_xml->startTag("columns");
		foreach($this->_columns as $columnName => $infosColumn)
		{
			$this->_xml->startTag("column");
			foreach($infosColumn as $key => $value)
			{
				if ((is_array($value) && !in_array($key,array("choices","values","errors"))) || in_array($key,array("values","errors")))
					continue;
					
				if ($key == "choices")
				{
					$this->_xml->startTag("choices");
					foreach($value as $currentValue)
						$this->_xml->addFullTag("choice",$currentValue,true);
					$this->_xml->endTag("choices");
				}
				else if ($key == "label")
				{
					$this->_xml->addFullTag($key,$value,true);
					$this->_xml->startTag("labels_html");
					$labels = explode(" ",trim($value));
					foreach($labels as $label)
						$this->_xml->addFullTag("label_html",$label,true);
					$this->_xml->endTag("labels_html");
				}
				else
					$this->_xml->addFullTag($key,$value,true);
			}
			$this->_xml->endTag("column");
		}
		$this->_xml->endTag("columns");
		# /Columns
		
		# Reload params		
		// Where
		$filters = $this->_http->getParam("filters");
		if (is_array($filters))
		{
			foreach($filters as $filterTable => $filterColumns)
			{
				foreach($filterColumns as $filterColumn => $infos)
				{	
					$values = (is_array($infos["values"]) && $this->_columns[$filterColumn]["html_type"] != 'input_checkbox') ? $infos["values"] : array($infos["values"]);
					$modes = (is_array($infos["mode"])) ? $infos["mode"] : array($infos["mode"]);
					for($i=0 ; $i<$count=count($values) ; $i++)
					{
						$value = $values[$i];
						$mode = (isset($modes[$i])) ? $modes[$i] : ((isset($modes[0])) ? ($modes[0]) : "");
						$mode = (empty($mode)) ? "equal" : $mode;
						if (is_array($value) && $this->_columns[$filterColumn]["html_type"] == 'input_checkbox')
							$mode = "in";
						
						if (in_array($mode,array("null","notnull")) || (!in_array($mode,array("null","notnull")) && !empty($value)))
						{
							$whereFound = false;
							foreach($this->_where as $where)
							{
								if (SLS_String::contains($where["column"],$filterColumn) && $where["value"] == $value && $where["mode"] == $mode)
								{
									$whereFound = true;
									break;
								}
							}
							if (!$whereFound)
								$this->_where[] = array("column" => $filterTable.".".$filterColumn, "value" => $value, "mode" => $mode);
						}
					}
				}
			}
		}
		// Order
		$orderP = $this->_http->getParam("Order");
		if (!empty($orderP))
		{
			$orderWays = array("ASC","DESC");			
			$orderColumn = SLS_String::substrBeforeLastDelimiter($orderP,"_");
			$orderWay = SLS_String::substrAfterLastDelimiter($orderP,"_");
			if (array_key_exists($orderColumn,$this->_columns))
			{
				if (!in_array(strtoupper($orderWay),$orderWays))
					$orderWay = array_shift($orderWays);
					
				$this->_order = array(array("column" => $orderColumn, "order" => $orderWay));
			}
		}
		// Limit
		$length = $this->_http->getParam("Length");
		if (!empty($length) && $length > 0)
			$this->_limit["length"] = $length;
		# /Reload params
		
		# Default params
		$positionExists = $this->_xmlType->getTag("//sls_configs/entry[@table='".$this->_table."' and @type='position']/@column");
		$lengthExists = $this->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='list_nb_by_page']");
		$fkRecursiveExists = $this->_xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($this->_db_alias."_".$this->_table)."' and @tablePk='".strtolower($this->_db_alias)."_".SLS_String::tableToClass($this->_table)."']/@columnFk");
		// i18n > restrict on current language
		if ($this->_object->isMultilanguage())
		{
			$whereLang = false;
			foreach($this->_where as $where)
			{
				if (SLS_String::contains($where["column"],"pk_lang"))
				{
					$whereLang = true;
					break;
				}
			}
			if (!$whereLang)
				array_unshift($this->_where, array("column" => $this->_table.".pk_lang", "value" => $this->_lang->getLang(), "mode" => "equal"));
				
			if (!empty($this->_join))
			{
				foreach($this->_join as $join)
				{
					$join = (is_array($join) && array_key_exists("table",$join)) ? $join["table"] : $join;
					$this->_generic->useModel(SLS_String::tableToClass($join),$this->_db_alias,"user");
					$joinClass = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($join);
					$joinObject = new $joinClass();
					$joinColumns = $joinObject->getColumns();
					if (is_array($joinColumns) && in_array("pk_lang",$joinColumns))
						array_push($this->_where, array("column" => $join.".pk_lang", "value" => $this->_lang->getLang(), "mode" => "equal"));
				}
			}
		}
		// fk on the same model
		if (!empty($fkRecursiveExists))
			$this->_where[] = array("column" => $this->_table.".".$fkRecursiveExists, "value" => "", "mode" => "null");
		// group by PK
		if (empty($this->_group))
			$this->_group = array($this->_object->getPrimaryKey());
		// order by position asc or PK desc
		if (empty($this->_order))
			$this->_order = (empty($positionExists)) ? array(array("column" => $this->_object->getPrimaryKey(), "order" => "DESC")) : array(array("column" => $positionExists, "order" => "ASC"));
		// limit at 0, 20
		if (empty($this->_limit))
		{
			if (!empty($lengthExists) && $lengthExists > 0)
				$this->_limit = array("start" => "0", "length" => $lengthExists);
			else
				$this->_limit = array("start" => "0", "length" => "20");
		}
		# /Default params
		
		# Page infos
		$this->_xml->startTag("page");	
			$this->_xml->startTag("model");
				$this->_xml->addFullTag("db",$this->_db_alias,true);
				$this->_xml->addFullTag("table",$this->_table,true);				
				$this->_xml->addFullTag("label",$comment = trim((empty($commentsTable[$this->_table])) ? $this->_table : $commentsTable[$this->_table]),true);
				$this->_xml->startTag("labels_html");
				$comments = explode(" ",$comment);
				foreach($comments as $comment)
					$this->_xml->addFullTag("label_html",$comment,true);
				$this->_xml->endTag("labels_html");
				$this->_xml->addFullTag("pk",$this->_object->getPrimaryKey(),true);
			$this->_xml->endTag("model");
			$this->_xml->startTag("joins");
			foreach($this->_join as $joinTable)
			{
				$this->_xml->startTag("join");
					$this->_xml->addFullTag("db",$this->_db_alias,true);
					$this->_xml->addFullTag("table",$joinTable["table"],true);
					$this->_xml->addFullTag("column",$joinTable["column"],true);
					$this->_xml->addFullTag("label",$comment = trim((empty($commentsTable[$joinTable["table"]])) ? $joinTable["table"] : $commentsTable[$joinTable["table"]]),true);
					$this->_xml->startTag("labels_html");
					$comments = explode(" ",$comment);
					foreach($comments as $comment)
						$this->_xml->addFullTag("label_html",$comment,true);
					$this->_xml->endTag("labels_html");
				$this->_xml->endTag("join");
			}
			$this->_xml->endTag("joins");
			$pkLangWhereFound = false;
			$this->_xml->startTag("wheres");
			foreach($this->_where as $clause)
			{
				$table  = (SLS_String::contains($clause["column"],".")) ? SLS_String::substrBeforeFirstDelimiter($clause["column"],".") : $this->_table;
				$column = (SLS_String::contains($clause["column"],".")) ? SLS_String::substrAfterFirstDelimiter($clause["column"],".") : $clause["column"];
				if ($column != "pk_lang" || ($column == "pk_lang" && !$pkLangWhereFound))
				{
					if ($column == "pk_lang" && !$pkLangWhereFound)
						$pkLangWhereFound = true;
					
					$this->_xml->startTag("where");
						$this->_xml->addFullTag("table",$table,true);
						$this->_xml->addFullTag("column",$column,true);
						$this->_xml->startTag("values");
						if (is_array($clause["value"]))
						{
							foreach($clause["value"] as $clauseValue)
								$this->_xml->addFullTag("value",$clauseValue,true);
						}
						else
							$this->_xml->addFullTag("value",$clause["value"],true);
						$this->_xml->endTag("values");
						$this->_xml->addFullTag("mode",$clause["mode"],true);
					$this->_xml->endTag("where");
				}
			}
			$this->_xml->endTag("wheres");
			$this->_xml->startTag("groups");
				foreach($this->_group as $groupColumn)
					$this->_xml->addFullTag("group",(SLS_String::contains($groupColumn,".")) ? SLS_String::substrAfterFirstDelimiter($groupColumn,".") : $groupColumn,true);
			$this->_xml->endTag("groups");
			$this->_xml->startTag("order");
				$this->_xml->addFullTag("column",(SLS_String::contains($this->_order[0]["column"],".")) ? SLS_String::substrAfterFirstDelimiter($this->_order[0]["column"],".") : $this->_order[0]["column"],true);
				$this->_xml->addFullTag("way",$this->_order[0]["order"],true);
			$this->_xml->endTag("order");
			$page = ($this->_http->getParam("page") > 1) ? $this->_http->getParam("page") : 1;
			$this->_xml->startTag("limit");
				$this->_xml->addFullTag("start",$this->_limit["start"] = ($page < 2) ? 0 : (($page-1) * $this->_limit["length"]),true);
				$this->_xml->addFullTag("length",$this->_limit["length"],true);
			$this->_xml->endTag("limit");
			$countWhere = $this->_where;
			if (!empty($fkRecursiveExists))
			{
				for($i=0 ; $i<$count=count($countWhere) ; $i++)
				{
					if ($countWhere[$i]["column"] == $this->_table.".".$fkRecursiveExists)
					{
						unset($countWhere[$i]);
						break;
					}
				}
			}
			$this->_xml->addFullTag("total",$this->_object->countModels($this->_table,$this->_join,$countWhere,$this->_group),true);
		$this->_xml->endTag("page");
		# /Page infos
				
		# Recordsets
		$recordsets = $this->_object->searchModels($this->_table,$this->_join,$this->_where,$this->_group,$this->_order,$this->_limit);
		$this->_xml = $this->formatRecordsets($this->_xml,$recordsets,$fkRecursiveExists);
		# Recordsets
		
		# Urls
		$this->_xml->startTag("urls");
			$this->_xml->addFullTag("list",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("read",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
			$this->_xml->addFullTag("add",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Add".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Add".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("add",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
			$this->_xml->addFullTag("populate",$this->_generic->getFullPath($this->_boController,"BoPopulate",array("Db" => ucfirst(strtolower($this->_db_alias)), "Table" => $this->_table)),true,array("authorized" => (SLS_BoRights::getAdminType() == "developer") ? "true" : "false"));
			$this->_xml->addFullTag("edit",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Modify".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Modify".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id" => ""),false) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("edit",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
			$this->_xml->addFullTag("clone",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Clone".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Clone".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id" => ""),false) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("clone",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
			$this->_xml->addFullTag("delete",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Delete".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Delete".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id" => ""),false) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("delete",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
		$this->_xml->endTag("urls");
		# /Urls
		
		# Remember admin settings
		$nodeExists = $this->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/@login");
		if (!empty($nodeExists))
		{
			$this->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='list_nb_by_page']",$this->_limit["length"]);
			$this->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
			$this->_xmlRight->refresh();
		}
		# /Remember admin settings
		
		# Session remember
		$rememberList = $this->_session->getParam("SLS_BO_LIST");
		if (empty($rememberList))
			$rememberList = array();
		$url = SLS_String::substrAfterFirstDelimiter($_SERVER["REQUEST_URI"],(($_SERVER['SCRIPT_NAME'] != "/index.php") ? SLS_String::substrBeforeFirstDelimiter($_SERVER['SCRIPT_NAME'],"/index.php")."/" : "/"));
		if (SLS_String::endsWith($url,$this->_generic->getSiteConfig("defaultExtension")))
			$url = SLS_String::substrBeforeLastDelimiter($url,".".$this->_generic->getSiteConfig("defaultExtension"));
		$query = http_build_query($_POST,"","/");
		$query = str_replace(array("%5B","%5D","=/","="),array("[","]","=|sls_empty|/","/"),preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query));		
		if (SLS_String::endsWith(trim($query),"/"))
			$query = SLS_String::substrBeforeLastDelimiter(trim($query),"/");
		if (!empty($query))
			$url .= "/".$query.((count(explode("/",$query))%2 != 0) ? "/|sls_empty|" : "");		
		if (SLS_String::endsWith($url,"/"))
			$url = SLS_String::substrBeforeLastDelimiter($url,"/");
		$url .= ".".$this->_generic->getSiteConfig("defaultExtension");
		$rememberList[$this->_db_alias."_".$this->_table] = $url;
		$this->_session->setParam("SLS_BO_LIST",$rememberList);		
		# /Session remember
		
		return $this->_xml;
	}
	
	/**
	 * Get children described by fk of the same model
	 * 
	 * @param SLS_XMLToolBox $xml	 
	 * @param string $fkColumn
	 * @param string $fkValue
	 * @return SLS_XMLToolbox $xml modified
	 */
	public function getFkChildrens($xml,$fkColumn,$fkValue)
	{	
		$erased = false;
		$whereChildrens = $this->_where;
		foreach($whereChildrens as $offset => $whereChildren)
		{			
			if ($whereChildren["column"] == $this->_table.".".$fkColumn)
			{
				$erased = true;
				$whereChildrens[$offset]["value"] = $fkValue;
				$whereChildrens[$offset]["mode"] = "equal";
			}
		}
		if ($erased)
		{	
			$childrens = $this->_object->searchModels($this->_table,$this->_join,$whereChildrens,$this->_group,$this->_order);
			$xml = $this->formatRecordsets($xml,$childrens,$fkColumn);
		}
		
		return $xml;
	}
	
	/**
	 * Format recordsets
	 * 
	 * @param SLS_XMLToolBox $xml
	 * @param array PDO $recordsets
	 * @param string $fkRecursiveExists
	 * @return SLS_XMLToolBox $xml modified
	 */
	public function formatRecordsets($xml,$recordsets,$fkRecursiveExists="")
	{	
		$xml->startTag("entities");
		for($i=0 ; $i<$count=count($recordsets) ; $i++)
		{
			$xml->startTag("entity",array("gap" => $this->_gap));
			foreach($recordsets[$i] as $key => $value)
			{	
				if (array_key_exists($key,$this->_columns) && ($this->_columns[$key]["list"] == "true" || $key == $this->_object->getPrimaryKey()))
				{
					$hashExists = $this->_xmlFilter->getTag("//sls_configs/entry[@table='".strtolower($this->_db_alias."_".$this->_columns[$key]["table"])."' and @column='".$key."' and @filter='hash']/@hash");
					
					if (!empty($this->_columns[$key]["specific_type"]))
					{
						switch($this->_columns[$key]["specific_type"])
						{
							case "email":
								if (SLS_String::validateEmail($value))
									$value = "<a href='mailto:".$value."' target='_blank' class='sls-bo-color-text'>".$value."</a>";
								break;
							case "url":
								if (SLS_String::isValidUrl($value))
									$value = "<a href='".$value."' target='_blank' class='sls-bo-color-text'>".SLS_String::substrAfterLastDelimiter($value,"://")."</a>";
								break;
							case "file";
								switch($this->_columns[$key]["specific_type_extended"])
								{
									case "all":
										if (!empty($value) && file_exists($this->_generic->getPathConfig("files").$value))
											$value = "<a href='".SLS_String::getUrlFile($value)."' target='_blank' class='sls-bo-color-text'>".SLS_String::substrAfterLastDelimiter($value,"/")."</a>";
										break;
									case "img":
										if (!empty($value) && file_exists($this->_generic->getPathConfig("files").$value))
											$value = "<a href='".SLS_String::getUrlFile($value)."' target='_blank'><img class='sls-image' sls-image-src='".((!empty($this->_columns[$key]["image_thumb"])) ? SLS_String::getUrlFileImg($value,$this->_columns[$key]["image_thumb"]) : SLS_String::getUrlFile($value))."' alt='".SLS_String::substrAfterLastDelimiter($value,"/")."' title='".SLS_String::substrAfterLastDelimiter($value,"/")."' /></a>";
										break;
								}
								break;
							case "color":
								$rgb = SLS_String::hex2RGB($value);
								$value = "<div class='sls-bo-box-color' style='color:".((((0.213 * $rgb["red"]) + (0.715 * $rgb["green"]) + (0.072 * $rgb["blue"])) < 0.5) ? "#FFF" : "#000").";background-color:#".$value."'>#".$value."</div>";
								break;
						}
					}
					if ($this->_columns[$key]["html_type"] == "input_textarea")
						$value = "<p>".SLS_String::trimStringToLength(strip_tags($value),150)."</p>";
					if (!empty($hashExists))
						$value = "********";
						
					$xml->addFullTag($key,$value,true);
				}
			}			
			if (!empty($fkRecursiveExists))
			{
				$this->_gap++;
				$xml = $this->getFkChildrens($xml,$fkRecursiveExists,$recordsets[$i]->{$this->_object->getPrimaryKey()});
				$this->_gap--;
			}
			$xml->endTag("entity");
		}
		$xml->endTag("entities");
		
		return $xml;
	}
}
?>