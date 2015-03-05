<?php
/**
* Class BoExport into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoExport extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		# Params
		$options = $this->_http->getParam("options");
		$model = $this->_http->getParam("model");
		// Format
		$this->_format = $options["format"];
		$this->_allColumn = $options["all_column"]; # true|false
		$this->_allTable = $options["all_table"]; # true|false
		$this->_legend = $options["display_legend"]; # true|false
		// Model
		$this->_db_alias = $model["db"];
		$this->_table = $model["table"];
		$this->_join = array();
		$this->_where = array();
		$this->_group = array();
		$this->_order = array();
		# /Params
				
		# Objects
		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($this->_db_alias)),"user");
		$this->_object = new $className();
		$this->_render = "";
		$xmlBoColors = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo_colors.xml"));
		$formats = array("excel","html","csv","txt");
		$this->_format = (in_array($this->_format,$formats)) ? $this->_format : array_shift($formats);
		$boColor = ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($this->_session->getParam("SLS_BO_USER")).'"]/settings/setting[@key="color"]') == null) ? "pink" : $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($this->_session->getParam("SLS_BO_USER")).'"]/settings/setting[@key="color"]');
		$boColors = array_shift($xmlBoColors->getTagsAttributes("//sls_configs/template[@name='".$boColor."']/color",array("th","td")));
		$boBgColors = array_shift($xmlBoColors->getTagsAttributes("//sls_configs/template[@name='".$boColor."']/bgcolor",array("th","td")));
		$this->_colors = array("bgcolor" => array("th" => $boBgColors["attributes"][0]["value"],
												  "td" => array("odd" 	=> SLS_String::substrBeforeFirstDelimiter($boBgColors["attributes"][1]["value"],"|"),
																"even" 	=> SLS_String::substrAfterFirstDelimiter($boBgColors["attributes"][1]["value"],"|"))),
							   "color" 	 => array("th" => $boColors["attributes"][0]["value"],
												  "td" => $boColors["attributes"][1]["value"]));
		# /Objects
		
		# Joins
		$joins = $this->_http->getParam("joins");
		if (is_array($joins))
		{		
			foreach($joins as $joinTable)
			{
				$classNameJoin = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($joinTable);
				$this->_generic->useModel(SLS_String::tableToClass($joinTable),ucfirst(strtolower($this->_db_alias)),"user");
				$objectJoin = new $classNameJoin();
				$this->_join[] = array("table" => $objectJoin->getTable(), "column" => $objectJoin->getPrimaryKey(), "mode" => "left");
			}
		}
		# /Joins
		
		# Where
		$filters = $this->_http->getParam("filters");
		if (is_array($filters))
		{
			foreach($filters as $filterTable => $filterColumns)
			{
				foreach($filterColumns as $filterColumn => $value)
				{
					$mode = (empty($value["mode"])) ? "equal" : $value["mode"];
					$value = implode(",",$value["values"]);
					var_dump($mode);
					var_dump($value);
					if ($this->_allTable == "false" && (in_array($mode,array("null","notnull")) || (!in_array($mode,array("null","notnull")) && !empty($value))))
						$this->_where[] = array("column" => $filterTable.".".$filterColumn, "value" => $value, "mode" => $mode);
				}
			}
		}
		# /Where
		
		# Order
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
					
				$this->_order = array($orderColumn => $orderWay);
			}
		}
		# /Order
		
		# Default params
		$positionExists = $this->_bo->_xmlType->getTag("//sls_configs/entry[@table='".$this->_table."' and @type='position']/@column");
		$fkRecursiveExists = $this->_bo->_xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($this->_db_alias."_".$this->_table)."' and @tablePk='".strtolower($this->_db_alias)."_".SLS_String::tableToClass($this->_table)."']/@columnFk");
		// group by PK
		if (empty($this->_group))
			$this->_group = array($this->_object->getPrimaryKey());
		// order by position asc or PK desc
		if (empty($this->_order))
			$this->_order = (empty($positionExists)) ? array($this->_object->getPrimaryKey() => "DESC") : array($positionExists => "ASC");		
		# /Default params
		
		# Comments & types
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
			$infosTable = $this->_bo->_db->showColumns($commentTable);			
			foreach($infosTable as $infoTable)
			{
				if (!array_key_exists($infoTable->Field,$this->_comments))				
				{
					$comment = empty($infoTable->Comment) ? $infoTable->Field : $infoTable->Comment;
					if (SLS_String::startsWith($comment,"sls:lang:"))
					{
						$key = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
						$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $infoTable->Field : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
					}
					$this->_comments[$infoTable->Field] = $comment;
				}
			}
		}		
		# Comments & types
		
		# Columns
		$this->_columns = array();
		$columnsDefault = $this->_object->getColumns();
		$columnsOptions = array("true","false");
		$columnsBo = $this->_bo->_xmlBo->getTagsAttributes("//sls_configs/entry[@type='table' and @name='".strtolower($className)."']/columns/column",array("table","name","displayFilter","displayList"));
		if (empty($columnsBo))
			$columnsBo = $this->_bo->_xmlBo->getTagsAttributes("//sls_configs/entry/entry[@type='table' and @name='".strtolower($className)."']/columns/column",array("table","name","displayFilter","displayList"));		
		if (empty($columnsBo))
		{			
			foreach($columnsDefault as $column)
				$this->_columns[$column] = array("table" => $this->_db_alias."_".$this->_table, "filter" => "true", "list" => "true");
		}
		else
		{
			for($i=0 ; $i<$count=count($columnsBo) ; $i++)
			{
				$colTable = $columnsBo[$i]["attributes"][0]["value"];
				$column = $columnsBo[$i]["attributes"][1]["value"];
				$filter = $columnsBo[$i]["attributes"][2]["value"];
				$list = ($this->_allColumn == "true") ? "true" : $columnsBo[$i]["attributes"][3]["value"];
				
				$this->_columns[$column] = array("table" => $colTable, "list" => (in_array($list,$columnsOptions)) ? $list : "true");
			}
		}		
		$xml->startTag("columns");
		foreach($this->_columns as $column => $infos)
		{
			$xml->startTag("column");
				$xml->addFullTag("db",SLS_String::substrBeforeFirstDelimiter($infos["table"],"_"),true);				
				$xml->addFullTag("table",SLS_String::substrAfterFirstDelimiter($infos["table"],"_"),true);				
				$xml->addFullTag("name",$column,true);
				$xml->addFullTag("label",(empty($this->_comments[$column])) ? $column : $this->_comments[$column],true);
				$xml->addFullTag("list",$infos["list"],true);				
			$xml->endTag("column");
		}
		$xml->endTag("columns");
		# /Columns
		
		# Remember admin settings
		$nodeExists = $this->_bo->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/@login");
		if (!empty($nodeExists))
		{			
			$this->_bo->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='export_format']",$this->_format);
			$this->_bo->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='export_all_column']",$this->_allColumn);
			$this->_bo->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='export_all_table']",$this->_allTable);
			$this->_bo->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='export_display_legend']",$this->_legend);			
			$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
			$this->_bo->_xmlRight->refresh();
		}
		# /Remember admin settings
		
		# Recordsets
		$recordsets = $this->_object->searchModels($this->_table,$this->_join,$this->_where,$this->_group,$this->_order,$this->_limit);
		$this->formatHeaders();
		$this->formatRecordsets($recordsets,$fkRecursiveExists);
		$this->formatRender();
		# /Recordsets
	}
	
	/**
	 * Format header content type and legend if wanted
	 * 
	 */
	public function formatHeaders()
	{
		$title = $this->_object->getTableComment($this->_table,$this->_db_alias)." Listing";
		switch($this->_format)
		{
			case "excel":
				header("Content-Type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=\"".$title.".xls\"");
				$this->_render = '<html>
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
									</head>
									<body style="font-family:\'Franklin Gothic Book\', Verdana, Helvetica, Arial;width:100%;margin:0;">
										<table style="border-collapse:collapse;width:100%;" cellpadding="5" width="100%">';
				if ($this->_legend == "true")
				{
					$this->_render .= 		'<tr>';
					foreach($this->_columns as $column => $infos)
					{
						if ($infos["list"] == "true")
						{
							$this->_render .= 	'<th style="text-align:center;background-color:'.$this->_colors["bgcolor"]["th"].';color:'.$this->_colors["color"]["th"].';border:1px solid '.$this->_colors["color"]["th"].';font-weight:bold;font-size:14px;">'
													.((empty($this->_comments[$column])) ? $column : $this->_comments[$column]).
												'</th>';
						}
					}
					$this->_render .= 		'</tr>';
				}				
				break;			
			case "html":				
				header("Content-Type: text/html");
				header("Content-Disposition: attachment; filename=\"".$title.".html\"");
				$this->_render = '<html>
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
									</head>
									<body style="font-family:\'Franklin Gothic Book\', Verdana, Helvetica, Arial;width:100%;">
										<table style="border-collapse:collapse;width:100%;" cellpadding="5" width="100%">';
				if ($this->_legend == "true")
				{
					$this->_render .= 		'<tr>';
					foreach($this->_columns as $column => $infos)
					{						
						if ($infos["list"] == "true")
						{							
							$this->_render .= 	'<th style="text-align:center;background-color:'.$this->_colors["bgcolor"]["th"].';color:'.$this->_colors["color"]["th"].';border:1px solid '.$this->_colors["color"]["th"].';font-weight:bold;font-size:14px;">'
													.((empty($this->_comments[$column])) ? $column : $this->_comments[$column]).
												'</th>';
						}
					}
					$this->_render .= 		'</tr>';
				}
				break;
			case "csv":
				header("Content-Type: text/csv");
				header("Content-Disposition: attachment; filename=\"".$title.".csv\"");
				if ($this->_legend == "true")
				{
					foreach($this->_columns as $column => $infos)
					{
						if ($infos["list"] == "true")
						{
							$this->_render .= 	str_replace(",","\,",((empty($this->_comments[$column])) ? $column : $this->_comments[$column])).";";
						}
					}
					$this->_render .= "\n";
				}				
				break;
			case "txt":
				header("Content-Type: text/tab-separated-value");
				header("Content-Disposition: attachment; filename=\"".$title.".tsv\"");
				if ($this->_legend == "true")
				{
					foreach($this->_columns as $column => $infos)
					{
						if ($infos["list"] == "true")
						{
							$this->_render .= 	str_replace(",","\,",((empty($this->_comments[$column])) ? $column : $this->_comments[$column]))."\t";
						}
					}
					$this->_render .= "\n";
				}
				break;
		}		
	}
	
	/**
	 * Get children described by fk of the same model
	 *  
	 * @param string $fkColumn
	 * @param string $fkValue
	 */
	public function getFkChildrens($fkColumn,$fkValue)
	{
		$erased = false;
		$whereChildrens = $this->_where;
		foreach($whereChildrens as $offset => $whereChildren)
		{			
			if ($whereChildren["column"] == $this->_table.".".$fkColumn)
			{
				$erased = true;
				$whereChildrens[$offset]["value"] = $fkValue;
			}
		}
		if ($erased)
		{
			$childrens = $this->_object->searchModels($this->_table,$this->_join,$whereChildrens,$this->_group,$this->_order);
			$this->formatRecordsets($childrens,$fkColumn);
		}
	}
	
	/**
	 * Format recordsets
	 * 
	 * @param array PDO $recordsets
	 * @param string $fkRecursiveExists
	 */
	public function formatRecordsets($recordsets,$fkRecursiveExists="")
	{
		for($i=0 ; $i<$count=count($recordsets) ; $i++)
		{			
			# Start line
			// If HTML output (Excel|HTML)
			if (in_array($this->_format,array("excel","html")))
				$this->_render .= 			'<tr style="background-color:'.(($i%2==0) ? $this->_colors["bgcolor"]["td"]["even"] : $this->_colors["bgcolor"]["td"]["odd"]).';color:'.$this->_colors["color"]["td"].';">';
			// If Plain output (Csv|Txt)
			else if (in_array($this->_format,array("csv","txt")))
				$this->_render .= 			'';
			# /Start line
			
			foreach($this->_columns as $key => $infos)
			{				
				$value = $recordsets[$i]->{$key};
				if ($infos["list"] == "true")
				{
					$valueProper = $value;
					if (array_key_exists($key,$this->_columns) && $this->_columns[$key]["list"] == "true")
					{
						$typeExists = $this->_bo->_xmlType->getTag("//sls_configs/entry[@table='".strtolower($this->_columns[$key]["table"])."' and @column='".$key."']/@type");
						$hashExists = $this->_bo->_xmlFilter->getTag("//sls_configs/entry[@table='".strtolower($this->_columns[$key]["table"])."' and @column='".$key."' and @filter='hash']/@hash");					
						switch($typeExists)
						{
							case "email":
								// If valid Email address
								if (SLS_String::validateEmail($value))
								{
									// If HTML output (Excel|HTML)
									if (in_array($this->_format,array("excel","html")))
										$value = "<a href='mailto:".$value."' style='color:".$this->_colors["bgcolor"]["th"].";' target='_blank'>".$value."</a>";
								}
								break;
							case "url":
								// If valid URL
								if (SLS_String::isValidUrl($value))
								{
									// If HTML output (Excel|HTML)
									if (in_array($this->_format,array("excel","html")))
										$value = "<a href='".$value."' style='color:".$this->_colors["bgcolor"]["th"].";' target='_blank'>".SLS_String::substrAfterLastDelimiter($value,"://")."</a>";
								}
								break;
							case "file_all";
								// If file exists
								if (!empty($value) && !is_dir($this->_generic->getPathConfig("files").$value) && file_exists($this->_generic->getPathConfig("files").$value))
								{
									// If HTML output (Excel|HTML)
									if (in_array($this->_format,array("excel","html")))
										$value = "<a href='".SLS_String::getUrlFile($value)."?".uniqid()."' style='color:".$this->_colors["bgcolor"]["th"].";' target='_blank'>".SLS_String::substrAfterLastDelimiter($value,"/")."</a>";
									// If Plain output (Csv|Txt)
									else if (in_array($this->_format,array("csv","txt")))
										$value = SLS_String::getUrlFile($value);
								}
								break;						
							case "file_img";
								$thumbsExists = $this->_bo->_xmlType->getTag("//sls_configs/entry[@table='".strtolower($this->_columns[$key]["table"])."' and @column='".$key."']/@thumbs");
								$suffix = "";
								if (!empty($thumbsExists))
								{
									$thumbsExists = unserialize(str_replace("||#||",'"',$thumbsExists));
									usort($thumbsExists,array($this->_bo,'sortThumbsMin'));
									$thumb = array_shift($thumbsExists);
									if (!empty($thumb["suffix"]))
										$suffix =  $thumb["suffix"];
								}
								// If file exists
								if (!empty($value) && !is_dir($this->_generic->getPathConfig("files").$value) && file_exists($this->_generic->getPathConfig("files").$value))
								{
									// If HTML output (Excel|HTML)
									if (in_array($this->_format,array("excel","html")))
									{
										$max_width = 100;
										$max_height = 0;									
										$img = $this->_generic->getPathConfig("files").$value;
										$width = array_shift(getimagesize($img));
										$height = array_shift(array_slice(getimagesize($img),1,1));
										if ($width > $height)
										{
											$max_width = 100;
											$max_height = floor($height*$max_width/$width);
										}
										else
										{
											$max_height = 100;
											$max_width = floor($width*$max_height/$height);
										}
										$value = "<a href='".SLS_String::getUrlFile($value)."?".uniqid()."' style='color:".$this->_colors["bgcolor"]["th"].";' target='_blank'><img src='".((!empty($suffix)) ? SLS_String::getUrlFileImg($value,$suffix) : SLS_String::getUrlFile($value))."?".uniqid()."' style='border:2px solid ".$this->_colors["bgcolor"]["th"].";' alt='".SLS_String::substrAfterLastDelimiter($value,"/")."' width='".$max_width."' style='max-width:".$max_width."px;'".((!empty($max_height)) ? " height='".$max_height."' style='max-height:".$max_height."px'" : "")." /></a>";									
									}
									// If Plain output (Csv|Txt)
									else if (in_array($this->_format,array("csv","txt")))
										$value = SLS_String::getUrlFile($value);
								}
								break;
							case "color":
								// If color set
								if (!empty($value))
								{
									// If HTML output (Excel|HTML)
									if (in_array($this->_format,array("excel","html")))
									{
										$rgb = SLS_String::hex2RGB($value);
										$value = "<div style='width:100%;height:100%;color:".((((0.213 * $rgb["red"]) + (0.715 * $rgb["green"]) + (0.072 * $rgb["blue"])) < 0.5) ? "#FFF" : "#000").";background-color:#".$value.";border:2px solid ".$this->_colors["bgcolor"]["th"].";'><br /><br />#".$value."</div>";
									}
									else
										$value = "#".$value;
								}
								break;
						}
						if (!empty($hashExists))
							$value = "********";
	
						if ($value == $valueProper)
							$value = strip_tags($value);
						
						# Column
						// If HTML output (Excel|HTML)
						if (in_array($this->_format,array("excel","html")))
							$this->_render .= 		'<td style="text-align:center;vertical-align:middle;border:1px solid #E7E8E9;color:'.$this->_colors["color"]["td"].';word-wrap:break-word;'.((!empty($max_width) && !empty($max_height)) ? 'max-width:'.$max_width.'px;max-height:'.$max_height.'px' : '').'"'.((!empty($max_width) && !empty($max_height)) ? ' width="'.$max_width.'" height="'.$max_height.'"' : '').'>'.((empty($value)) ? "&nbsp;" : $value).'</td>';
						// If Csv
						else if (in_array($this->_format,array("csv")))
							$this->_render .= 		'"'.str_replace(array('"',"\n","\t","\r"),array('""','','',''),$value).'";';
						// If Txt
						else if (in_array($this->_format,array("txt")))
							$this->_render .= 		str_replace(array("\n","\t","\r"),array("","",""),$value)."\t";
						# /Column
					}
				}
			}
			
			# End line
			// If HTML output (Excel|HTML)
			if (in_array($this->_format,array("excel","html")))
				$this->_render .= 			'</tr>';
			// If Plain output (Csv|Txt)
			else if (in_array($this->_format,array("csv","txt")))
				$this->_render .= 			"\n";
			# /End line
						
			if (!empty($fkRecursiveExists))
				 $this->getFkChildrens($fkRecursiveExists,$recordsets[$i]->{$this->_object->getPrimaryKey()});
		}
	}
	
	/**
	 * Format footer and display exported file
	 * 
	 */
	public function formatRender()
	{
		// If HTML output (Excel|HTML)
		if (in_array($this->_format,array("excel","html")))
		{
			$this->_render .= '		</table>
								</body>
							</html>';
		}
		echo $this->_render;
		die();	
	}
}
?>