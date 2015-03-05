<?php
/**
 * SLS_BoAdd Tool - Generate back-office adding
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package SLS.Generics.Tools.SLS_Bo
 * @since 1.1
 */
class SLS_BoAdd extends __SLS_Bo
{		
	public $_xml = null;
	public $_db_alias = null;
	public $_table = null;
	public $_forward = true;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct($xml,$db,$table,$forward=true)
	{
		parent::__construct();
		
		$this->_xml = $xml;
		$this->_db_alias = $db;
		$this->_table = $table;
		$this->_forward = $forward;
	}
	
	public function getXML()
	{
		# Objects
		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($this->_db_alias)),"user");
		$this->_object = new $className();
		$this->_table = $this->_object->getTable();
		$this->_langsValues = array();
		if ($this->_object->isMultilanguage())
			foreach($this->_langs as $lang)
				$this->_langsValues[$lang] = array();
		else
			$this->_langsValues[$this->_defaultLang] = array();
		$this->_boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($className)."']";
		$boExists = $this->_xmlBo->getTag($this->_boPath);
		if (empty($boExists))		
			$this->_boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($className)."']";
		$this->_db->changeDb($this->_db_alias);
		$redirects = array("list","add","edit");
		# /Objects
		
		# Model comment
		$this->_tableComment = $this->_object->getTableComment($this->_table,$this->_db_alias);		
		if (SLS_String::startsWith($this->_tableComment,"sls:lang:"))
		{
			$key = strtoupper(SLS_String::substrAfterFirstDelimiter($this->_tableComment,"sls:lang:"));
			$this->_tableComment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $this->_table : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
		}
		if (empty($this->_tableComment))
			$this->_tableComment = $this->_table;
		# /Model comment
			
		# Columns definitions
		$this->_columns = array();
		$this->_bearers = array();
		$this->_children = array();
		
		// Columns
		$this->_columns = $this->getTableColumns($this->_db_alias,$this->_table,$this->_boPath);
		
		// Bearers
		$bearers = $this->_xmlBearer->getTagsAttributes("//sls_configs/entry[@table1='".$className."']",array("tableBearer","table2"));
		if (!empty($bearers))
		{
			for($i=0 ; $i<$count=count($bearers) ; $i++)
			{
				$bearerDb = ucfirst(SLS_String::substrBeforeFirstDelimiter($bearers[$i]["attributes"][0]["value"],"_"));
				$bearerTable = SLS_String::substrAfterFirstDelimiter($bearers[$i]["attributes"][0]["value"],"_");
				$bearerClass = $bearers[$i]["attributes"][0]["value"];
				$bearerTargetDb = ucfirst(SLS_String::substrBeforeFirstDelimiter($bearers[$i]["attributes"][1]["value"],"_"));
				$bearerTargetUse = SLS_String::substrAfterFirstDelimiter($bearers[$i]["attributes"][1]["value"],"_");
				$bearerTargetClass = $bearers[$i]["attributes"][1]["value"];
				$this->_generic->useModel($bearerTable,$bearerDb,"user");
				$this->_generic->useModel($bearerTargetUse,$bearerTargetDb,"user");
				$bearerObject = new $bearerClass();
				$bearerTargetObject = new $bearerTargetClass();
				$bearerTargetPk = $bearerTargetObject->getPrimaryKey();
				$resultFk = array_shift($this->_xmlFk->getTagsAttribute("//sls_configs/entry[@tableFk='".strtolower($bearerClass)."' and @columnFk='".$bearerTargetObject->getPrimaryKey()."' and @tablePk = '".strtolower($bearerTargetDb)."_".$bearerTargetUse."']","labelPk"));
				$labelFk = (empty($resultFk)) ? $bearerTargetObject->getPrimaryKey() : $resultFk["attribute"];
				$labelFkReal = $labelFk;
				$str = $labelFk;
				$values = array();
				$masks = array();
				foreach($bearerTargetObject->getParams() as $key => $value)		
					array_push($masks,$key);				
				foreach($bearerTargetObject->getParams() as $key => $value)					
					if (SLS_String::contains($labelFk,$key))
						$labelFk = str_replace($key,$bearerTargetObject->getColumnComment($key),$labelFk);
				
				$this->_bearers[$bearerObject->getTable()] = array("table" 						=> $bearerObject->getTable(),
																   "name" 						=> $bearerTargetPk,
																   "label" 						=> $bearerObject->getTableComment($bearerObject->getTable(),$bearerDb),
																   "multilanguage" 				=> "false",
																   "native_type" 				=> "int",
																   "html_type" 					=> "input_ac",
																   "specific_type" 				=> "",
																   "specific_type_extended" 	=> "",
																   "file_uid"					=> uniqid(),
																   "image_ratio" 				=> "*",
																   "image_min_width" 			=> "*",
																   "image_min_height" 			=> "*",
																   "html" 						=> "false",
																   "choices" 					=> array(),
																   "values" 					=> array(),
																   "errors"						=> array(),
																   "required" 					=> "false",
																   "unique" 					=> "false",				
																   "default" 					=> "",
																   "ac_db" 						=> strtolower($bearerDb),
																   "ac_entity" 					=> strtolower($bearerTable),
																   "ac_fk"						=> $bearerTargetClass,
																   "ac_column" 					=> $bearerTargetPk,
																   "ac_label" 					=> $labelFk,
																   "ac_pattern" 				=> $labelFkReal,
																   "ac_multiple" 				=> "true",
																   "min_length" 				=> "",
																   "max_length" 				=> "",
																   "filters" 					=> "");
			}
		}
		
		// Children
		$children = $this->_xmlBo->getTagsAttributes($this->_boPath."/children/child",array("table","column"));
		for($i=0 ; $i<$count=count($children) ; $i++)
		{
			$childTable = SLS_String::substrAfterFirstDelimiter($children[$i]["attributes"][0]["value"],"_");
			$childDb = ucfirst(strtolower(SLS_String::substrBeforeFirstDelimiter($children[$i]["attributes"][0]["value"],"_")));
			if ($this->_db->tableExists($childTable) && SLS_BoRights::isAuthorized("add",$childDb."_".SLS_String::tableToClass($childTable)))
			{
				$this->_generic->useModel(SLS_String::tableToClass($childTable),$childDb,"user");
				$childClassName = $childDb."_".SLS_String::tableToClass($childTable);
				$childObject = new $childClassName();
				$childPath = "//sls_configs/entry[@type='table' and @name='".strtolower($childClassName)."']";
				$childExists = $this->_xmlBo->getTag($childPath);
				if (empty($childExists))		
					$childPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($childClassName)."']";
				
				$childComment = $this->_object->getTableComment($childTable,$this->_db_alias);		
				if (SLS_String::startsWith($childComment,"sls:lang:"))
				{
					$key = strtoupper(SLS_String::substrAfterFirstDelimiter($childComment,"sls:lang:"));
					$childComment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $childTable : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
				}
				if (empty($childComment))
					$childComment = $childTable;
				
				$this->_children[$childTable] = array("model" 	=> array("db" 				=> strtolower($childDb),
																		 "table" 			=> $childTable,
																		 "label" 			=> $childComment,
																		 "nbChildren" 		=> 0,
																		 "multilanguage" 	=> ($childObject->isMultilanguage()) ? "true" : "false",
																		 "pk" 				=> $childObject->getPrimaryKey()),
													  "urls"	=> array("list" 		=> array("url" => ($this->_generic->getActionId($this->_boController,"List".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable)) != null) 		? $this->_generic->getFullPath($this->_boController,"List".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable),array(),true) 			: "", "authorized" => (SLS_BoRights::isAuthorized("read",ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable))) ? "true" : "false"),
													  					 "add" 			=> array("url" => ($this->_generic->getActionId($this->_boController,"Add".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable)) != null) 		? $this->_generic->getFullPath($this->_boController,"Add".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable),array(),true) 			: "", "authorized" => (SLS_BoRights::isAuthorized("add",ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable))) ? "true" : "false"),
																		 "populate"		=> array("url" => ($this->_generic->getActionId($this->_boController,"BoPopulate".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable)) != null) ? $this->_generic->getFullPath($this->_boController,"Populate".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable),array(),true) 		: "", "authorized" => (SLS_BoRights::getAdminType() == "developer") ? "true" : "false"),
																		 "edit" 		=> array("url" => ($this->_generic->getActionId($this->_boController,"Modify".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable)) != null) 	? $this->_generic->getFullPath($this->_boController,"Modify".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable),array("id"=>""),false) : "", "authorized" => (SLS_BoRights::isAuthorized("edit",ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable))) ? "true" : "false"),
																		 "clone" 		=> array("url" => ($this->_generic->getActionId($this->_boController,"Clone".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable)) != null) 		? $this->_generic->getFullPath($this->_boController,"Clone".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable),array("id"=>""),false) 	: "", "authorized" => (SLS_BoRights::isAuthorized("clone",ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable))) ? "true" : "false"),
																		 "delete" 		=> array("url" => ($this->_generic->getActionId($this->_boController,"Delete".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable)) != null) 	? $this->_generic->getFullPath($this->_boController,"Delete".ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable),array("id"=>""),false) : "", "authorized" => (SLS_BoRights::isAuthorized("delete",ucfirst(strtolower($childDb))."_".SLS_String::tableToClass($childTable))) ? "true" : "false")),
													  "columns" => $this->getTableColumns($this->_db_alias,$childTable,$childPath,$className),
													  "bearers" => array());
				// Bearers
				$bearers = $this->_xmlBearer->getTagsAttributes("//sls_configs/entry[@table1='".$childClassName."']",array("tableBearer","table2"));
				if (!empty($bearers))
				{
					for($j=0 ; $j<$countJ=count($bearers) ; $j++)
					{
						$bearerDb = ucfirst(SLS_String::substrBeforeFirstDelimiter($bearers[$j]["attributes"][0]["value"],"_"));
						$bearerTable = SLS_String::substrAfterFirstDelimiter($bearers[$j]["attributes"][0]["value"],"_");
						$bearerClass = $bearers[$j]["attributes"][0]["value"];
						$bearerTargetDb = ucfirst(SLS_String::substrBeforeFirstDelimiter($bearers[$j]["attributes"][1]["value"],"_"));
						$bearerTargetUse = SLS_String::substrAfterFirstDelimiter($bearers[$j]["attributes"][1]["value"],"_");
						$bearerTargetClass = $bearers[$j]["attributes"][1]["value"];
						$this->_generic->useModel($bearerTable,$bearerDb,"user");
						$this->_generic->useModel($bearerTargetUse,$bearerTargetDb,"user");
						$bearerObject = new $bearerClass();
						$bearerTargetObject = new $bearerTargetClass();
						$bearerTargetPk = $bearerTargetObject->getPrimaryKey();
						$resultFk = array_shift($this->_xmlFk->getTagsAttribute("//sls_configs/entry[@tableFk='".strtolower($bearerClass)."' and @columnFk='".$bearerTargetObject->getPrimaryKey()."' and @tablePk = '".strtolower($bearerTargetDb)."_".$bearerTargetUse."']","labelPk"));
						$labelFk = (empty($resultFk)) ? $bearerTargetObject->getPrimaryKey() : $resultFk["attribute"];
						$labelFkReal = $labelFk;
						$str = $labelFk;
						$values = array();
						$masks = array();
						foreach($bearerTargetObject->getParams() as $key => $value)		
							array_push($masks,$key);				
						foreach($bearerTargetObject->getParams() as $key => $value)					
							if (SLS_String::contains($labelFk,$key))
								$labelFk = str_replace($key,$bearerTargetObject->getColumnComment($key),$labelFk);
						
						$this->_children[$childTable]["bearers"][$bearerObject->getTable()] = array("table" 					=> $bearerObject->getTable(),
																					 				"name" 						=> $bearerTargetPk,
																					 				"label" 					=> $bearerObject->getTableComment($bearerObject->getTable(),$bearerDb),
																					 				"multilanguage" 			=> "false",
																					 				"native_type" 				=> "int",
																					 				"html_type" 				=> "input_ac",
																					 				"specific_type" 			=> "",
																					 				"specific_type_extended" 	=> "",
																					 				"file_uid"					=> uniqid(),
																					 				"image_ratio" 				=> "*",
																					 				"image_min_width" 			=> "*",
																					 				"image_min_height" 			=> "*",
																					 				"html" 						=> "false",
																					 				"choices" 					=> array(),
																					 				"values" 					=> array(),
																					 				"errors"					=> array(),
																					 				"required" 					=> "false",
																					 				"unique" 					=> "false",				
																					 				"default" 					=> "",
																					 				"ac_db" 					=> strtolower($bearerDb),
																					 				"ac_entity" 				=> strtolower($bearerTable),
																					 				"ac_fk"						=> $bearerTargetClass,
																					 				"ac_column" 				=> $bearerTargetPk,
																					 				"ac_label" 					=> $labelFk,
																					 				"ac_pattern" 				=> $labelFkReal,
																					 				"ac_multiple" 				=> "true",
																					 				"min_length" 				=> "",
																					 				"max_length" 				=> "",
																					 				"filters" 					=> "");
					}
				}
			}
		}
		# /Columns definitions

		# Reload
		$this->_error = false;
		$this->_object_id = $this->_object->giveNextId();
		$this->_recordsets = array();
		if ($this->_http->getParam("reload-add") == "true")
		{
			$modelParams 	= (is_array($this->_http->getParam($this->_table))) ? $this->_http->getParam($this->_table) : array();
			$properties 	= (is_array($modelParams["properties"])) ? $modelParams["properties"] : array();
			$bearers 		= (is_array($modelParams["bearers"])) ? $modelParams["bearers"] : array();
			$children 		= (is_array($modelParams["children"])) ? $modelParams["children"] : array();
			$redirect 		= (in_array(strtolower($this->_http->getParam("redirect")),$redirects)) ? strtolower($this->_http->getParam("redirect")) : array_shift($redirects);
			
			# MAIN MODEL
			uksort($properties,array($this, 'unshiftDefaultLang'));
			foreach($properties as $lang => $columns)
			{
				$this->_object->setModelLanguage($lang);
				
				if (!empty($columns))
				{
					foreach(((is_array($columns)) ? $columns : array($columns)) as $column => $value)
					{	
						$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$column)," ",false)),"");
						
						// Remember values
						if (is_array($value) && array_key_exists("file",$value))
						{
							$value = $value["file"];
							
							if (!is_array($value))
								$value = SLS_String::substrAfterFirstDelimiter($value,$this->_generic->getPathConfig("files"));
						}
						else if (is_array($value))
						{
							$this->_columns[$column]["values"][$lang] = $value;
							
							// MySQL Type Set
							if ($this->_columns[$column]["specific_type"] != 'file')
								$value = implode(",",$value);
						}
						else
						{	
							// Check FK
							if (!empty($value) && $this->_columns[$column]["html_type"] == 'input_ac')
							{
								$fkAlias = ucfirst(strtolower(SLS_String::substrBeforeFirstDelimiter($this->_columns[$column]["ac_fk"],"_")));
								$fkModel = SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($this->_columns[$column]["ac_fk"],"_"));
								$fkClass = $fkAlias."_".$fkModel;
								$this->_generic->useModel($fkModel,$fkAlias,"user");
								$fkObject = new $fkClass();
								
								if ($fkObject->getModel($value) == true)
								{
									$replacements = $fkObject->getParams();
									$fkLabel = $this->_columns[$column]["ac_pattern"];
									foreach($replacements as $keyFk => $valueFk)
										$fkLabel = str_replace($keyFk, $valueFk, $fkLabel);
										
									$this->_columns[$column]["values"][$lang][] = array("label" => $fkLabel, "value" => $value);
								}
								else
									$this->_columns[$column]["values"][$lang][] = "";
							}
							else
								$this->_columns[$column]["values"][$lang][] = $value;
						}
						
						// Setter
						if (!$this->_object->$functionName($value))
						{
							$this->_error = true;
							$this->_columns[$column]["errors"][$lang] = $this->_object->getError($column);
						}
						
						// Remember value type file
						if ($this->_columns[$column]["html_type"] == 'input_file')
						{
							// No error, take model value
							if (empty($this->_columns[$column]["errors"][$lang]))
							{
								$fileValue = $this->_object->__get($column);
								$this->_columns[$column]["values"][$lang][] = (!empty($fileValue)) ? SLS_String::getUrlFile($fileValue) : "";
							}
							// Else, take uploaded file
							else
							{
								// Modern browsers
								if (is_array($value) && array_key_exists("data",$value))
									$value = $value["data"];
								
								if (is_array($value) && array_key_exists("tmp_name",$value))
									$this->_columns[$column]["values"][$lang][] = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$value["tmp_name"];
							}
						}
					}
				}
				
				$this->_object->create($this->_object_id);
			}
			# /MAIN MODEL
			
			# CHILDREN
			foreach($children as $childTable => $childValues)
			{
				$this->_generic->useModel(SLS_String::tableToClass($childTable),$this->_db_alias,"user");
				$childClassName = $this->_db_alias."_".SLS_String::tableToClass($childTable);
				$childObject = new $childClassName();
				$this->_recordsets[$childTable]["pk"] = $childObject->getPrimaryKey();
				$this->_children[$childTable]["model"]["nbChildren"] = (is_array($childValues)) ? count($childValues) : 1;
				
				foreach(((is_array($childValues)) ? $childValues : array($childValues)) as $childItem => $infos)
				{
					// Child
					$properties = (is_array($infos["properties"])) ? $infos["properties"] : array($infos["properties"]);
					$this->_recordsets[$childTable]["ids"][] = $childId = $childObject->giveNextId();	
					
					uksort($properties,array($this, 'unshiftDefaultLang'));
					foreach($properties as $lang => $columns)
					{
						$childObject->setModelLanguage($lang);
						
						foreach(((is_array($columns)) ? $columns : array($columns)) as $column => $value)
						{
							$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$column)," ",false)),"");
							
							// Remember values
							if (is_array($value) && array_key_exists("file",$value))
							{
								$value = $value["file"];
								
								if (!is_array($value))
									$value = SLS_String::substrAfterFirstDelimiter($value,$this->_generic->getPathConfig("files"));
							}
							else if (is_array($value))
							{
								$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang] = $value;
								
								// MySQL Type Set
								if ($this->_children[$childTable]["columns"][$column]["specific_type"] != 'file')
									$value = implode(",",$value);
							}
							else
							{	
								// Check FK
								if (!empty($value) && $this->_children[$childTable]["columns"][$column]["html_type"] == 'input_ac')
								{
									$fkAlias = ucfirst(strtolower(SLS_String::substrBeforeFirstDelimiter($this->_children[$childTable]["columns"][$column]["ac_fk"],"_")));
									$fkModel = SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($this->_children[$childTable]["columns"][$column]["ac_fk"],"_"));
									$fkClass = $fkAlias."_".$fkModel;
									$this->_generic->useModel($fkModel,$fkAlias,"user");
									$fkObject = new $fkClass();
									
									if ($fkObject->getModel($value) == true)
									{
										$replacements = $fkObject->getParams();
										$fkLabel = $this->_children[$childTable]["columns"][$column]["ac_pattern"];
										foreach($replacements as $keyFk => $valueFk)
											$fkLabel = str_replace($keyFk, $valueFk, $fkLabel);
											
										$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang][] = array("label" => $fkLabel, "value" => $value);
									}
									else
										$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang][] = "";
								}
								else
									$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang][] = $value;
							}
							
							// Setter
							if (!$childObject->$functionName($value))
							{
								$this->_error = true;
								$this->_children[$childTable]["columns"][$column]["errors"][$childItem][$lang] = $childObject->getError($column);
							}
							else
								$this->_children[$childTable]["columns"][$column]["errors"][$childItem][$lang] = "";
							
							// Remember value type file
							if ($this->_children[$childTable]["columns"][$column]["html_type"] == 'input_file')
							{
								// No error, take model value
								if (empty($this->_children[$childTable]["columns"][$column]["errors"][$childItem][$lang]))
								{
									$fileValue = $childObject->__get($column);
									$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang][] = (!empty($fileValue)) ? SLS_String::getUrlFile($fileValue) : "";
								}
								// Else, take uploaded file
								else
								{
									// Modern browsers
									if (is_array($value) && array_key_exists("data",$value))
										$value = $value["data"];
									
									if (is_array($value) && array_key_exists("tmp_name",$value))
										$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang][] = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$value["tmp_name"];
									else
										$this->_children[$childTable]["columns"][$column]["values"][$childItem][$lang][] = "";
								}
							}
						}
						
						// Force fk setter
						$fkColumn = $this->_xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($childClassName)."' and @tablePk='".$this->_db_alias."_".SLS_String::tableToClass($this->_table)."']/@columnFk");
						if (!empty($fkColumn))
						{
							$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$fkColumn)," ",false)),"");
							$childObject->$functionName($this->_object_id);
						}
						
						$childObject->create($childId);
					}
					
					$childObject->clear();
					
					// Bearers
					$childBearers = (!empty($infos["bearers"])) ? ((is_array($infos["bearers"])) ? $infos["bearers"] : array($infos["bearers"])) : array();
					foreach($childBearers as $bearerTable => $bearerValues)
					{
						// Bearer object
						$bearerClass = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($bearerTable);
						$bearerAttributes = array_shift($this->_xmlBearer->getTagsAttributes("//sls_configs/entry[@tableBearer='".$bearerClass."']",array("table1","table2")));
						$this->_generic->useModel(SLS_String::tableToClass($bearerTable),$this->_db_alias,"user");
						$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($bearerAttributes["attributes"][0]["value"],"_"),SLS_String::substrBeforeFirstDelimiter($bearerAttributes["attributes"][0]["value"],"_"));
						$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($bearerAttributes["attributes"][1]["value"],"_"),SLS_String::substrBeforeFirstDelimiter($bearerAttributes["attributes"][1]["value"],"_"));
						$bearerObject = new $bearerClass();
						$this->_recordsets[$bearerObject->getTable()]["pk"] = $bearerObject->getPrimaryKey();
						$objectBearerTarget1 = new $bearerAttributes["attributes"][0]["value"]();
						$objectBearerTarget2 = new $bearerAttributes["attributes"][1]["value"]();				
						$setterBearerTarget1 = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$objectBearerTarget1->getPrimaryKey())," ",false)),"");
						$setterBearerTarget2 = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$objectBearerTarget2->getPrimaryKey())," ",false)),"");
						$fkColumn = $this->_xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($bearerObject->getDatabase())."_".$bearerObject->getTable()."' and @tablePk='".strtolower($bearerObject->getDatabase())."_".SLS_String::substrAfterFirstDelimiter($bearerAttributes["attributes"][0]["value"],"_")."']/@columnFk");
												
						// Delete old bearers
						$bearerObject->deleteModels($bearerObject->getTable(),array(),array(0=>array("column"=>$fkColumn,"value"=>$childId,"mode"=>"equal")));
						
						// Save new bearers
						foreach($bearerValues as $bearerValue)
						{	
							if ($objectBearerTarget2->getModel($bearerValue) === true)
							{
								$replacements = $objectBearerTarget2->getParams();
								$bearerLabel = $this->_children[$childTable]["bearers"][$bearerObject->getTable()]["ac_pattern"];
								foreach($replacements as $keyBearer => $valueBearer)
									$bearerLabel = str_replace($keyBearer, $valueBearer, $bearerLabel);
								
								$this->_children[$childTable]["bearers"][$bearerObject->getTable()]["values"][$childItem][] = array("label" => $bearerLabel, "value" => $bearerValue);
								
								if (!$this->_error)
								{
									$bearerObject->$setterBearerTarget1($childId);
									$bearerObject->$setterBearerTarget2($bearerValue);
									$bearerObjectId = $bearerObject->create();
									$this->_recordsets[$bearerObject->getTable()]["ids"][] = $bearerObjectId;
									$bearerObject->clear();
								}
							}
						}
					}
				}
			}
			# /CHILDREN
			
			# BEARERS
			foreach($bearers as $bearerTable => $bearerValues)
			{
				// Bearer object
				$bearerClass = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($bearerTable);
				$bearerAttributes = array_shift($this->_xmlBearer->getTagsAttributes("//sls_configs/entry[@tableBearer='".$bearerClass."']",array("table1","table2")));
				$this->_generic->useModel(SLS_String::tableToClass($bearerTable),$this->_db_alias,"user");
				$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($bearerAttributes["attributes"][0]["value"],"_"),SLS_String::substrBeforeFirstDelimiter($bearerAttributes["attributes"][0]["value"],"_"));
				$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($bearerAttributes["attributes"][1]["value"],"_"),SLS_String::substrBeforeFirstDelimiter($bearerAttributes["attributes"][1]["value"],"_"));
				$bearerObject = new $bearerClass();
				$this->_recordsets[$bearerObject->getTable()]["pk"] = $bearerObject->getPrimaryKey();
				$objectBearerTarget1 = new $bearerAttributes["attributes"][0]["value"]();
				$objectBearerTarget2 = new $bearerAttributes["attributes"][1]["value"]();				
				$setterBearerTarget1 = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$objectBearerTarget1->getPrimaryKey())," ",false)),"");
				$setterBearerTarget2 = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$objectBearerTarget2->getPrimaryKey())," ",false)),"");
				$fkColumn = $this->_xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($bearerObject->getDatabase())."_".$bearerObject->getTable()."' and @tablePk='".strtolower($bearerObject->getDatabase())."_".SLS_String::substrAfterFirstDelimiter($bearerAttributes["attributes"][0]["value"],"_")."']/@columnFk");
				
				// Delete old bearers
				$bearerObject->deleteModels($bearerObject->getTable(),array(),array(0=>array("column"=>$fkColumn,"value"=>$this->_object_id,"mode"=>"equal")));
				
				// Save new bearers
				foreach($bearerValues as $bearerValue)
				{	
					if ($objectBearerTarget2->getModel($bearerValue) === true)
					{
						$replacements = $objectBearerTarget2->getParams();
						$bearerLabel = $this->_bearers[$bearerObject->getTable()]["ac_pattern"];
						foreach($replacements as $keyBearer => $valueBearer)
							$bearerLabel = str_replace($keyBearer, $valueBearer, $bearerLabel);
						
						$this->_bearers[$bearerObject->getTable()]["values"][] = array("label" => $bearerLabel, "value" => $bearerValue);
						
						if (!$this->_error)
						{
							$bearerObject->$setterBearerTarget1($this->_object_id);
							$bearerObject->$setterBearerTarget2($bearerValue);
							$bearerObjectId = $bearerObject->create();
							$this->_recordsets[$bearerObject->getTable()]["ids"][] = $bearerObjectId;
							$bearerObject->clear();
						}
					}
				}
			}
			# /BEARERS
			
			# If error, delete model & linked recordsets
			if ($this->_error && $this->_object->getModel($this->_object_id))
			{
				// Delete main model
				$this->_object->delete(true);
				
				// Delete other recordsets
				foreach($this->_recordsets as $recordsetTable => $recordsetInfos)
				{
					$recordsetIds = (array_key_exists("ids",$recordsetInfos) && is_array($recordsetInfos["ids"])) ? $recordsetInfos["ids"] : ((empty($recordsetInfos["ids"])) ? array() : array($recordsetInfos["ids"]));
					$recordsetPk = (array_key_exists("pk",$recordsetInfos) && !empty($recordsetInfos["pk"])) ? $recordsetInfos["pk"] : "";
					if (!empty($recordsetPk) && !empty($recordsetIds))
					{
						$sql = "DELETE FROM `".$recordsetTable."` WHERE `".$recordsetPk."` IN (".implode(",",$recordsetIds).") ";
						$this->_db->delete($sql);
					}
				}
			}
			# /Errors
			
			# All good dude !
			if (!$this->_error)
			{
				$this->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_ADD']);
				
				if ($this->_forward)
				{	
					switch ($redirect)
					{
						case "edit":
							# Remember admin settings
							$nodeExists = $this->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/@login");
							if (!empty($nodeExists))
							{
								$this->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='add_callback']","edit");
								$this->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
								$this->_xmlRight->refresh();
							}
							# /Remember admin settings
							$this->_generic->forward($this->_boController,"Modify".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id"=>$this->_object_id));
							break;
						case "add":
							# Remember admin settings
							$nodeExists = $this->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/@login");
							if (!empty($nodeExists))
							{
								$this->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='add_callback']","add");
								$this->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
								$this->_xmlRight->refresh();
							}
							# /Remember admin settings
							$this->_generic->forward($this->_boController,"Add".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
							break;
						case "list":
							# Remember admin settings
							$nodeExists = $this->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/@login");
							if (!empty($nodeExists))
							{
								$this->_xmlRight->setTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting[@key='add_callback']","list");
								$this->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
								$this->_xmlRight->refresh();
							}
							# /Remember admin settings
							$rememberList = (is_array($this->_session->getParam("SLS_BO_LIST"))) ? $this->_session->getParam("SLS_BO_LIST") : array();
							if (array_key_exists($this->_db_alias."_".$this->_table,$rememberList) && !empty($rememberList[$this->_db_alias."_".$this->_table]))
								$this->_generic->redirect($rememberList[$this->_db_alias."_".$this->_table]);
							else
								$this->_generic->forward($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
							break;
					}
				}
			}
			# /All good dude !
		}
		# /Reload
		
		# Page infos
		$langsError = array();
		$this->_xml->startTag("page");	
			$this->_xml->startTag("model");
				$this->_xml->addFullTag("db",$this->_db_alias,true);
				$this->_xml->addFullTag("table",$this->_table,true);				
				$this->_xml->addFullTag("label",$this->_tableComment,true);
				$this->_xml->addFullTag("multilanguage",($this->_object->isMultilanguage()) ? "true" : "false",true);
				$this->_xml->addFullTag("pk",$this->_object->getPrimaryKey(),true);
			$this->_xml->endTag("model");
			$this->_xml->startTag("urls");
				$this->_xml->addFullTag("list",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("read",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
				$this->_xml->addFullTag("add",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Add".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Add".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("add",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
				$this->_xml->addFullTag("populate",$this->_generic->getFullPath($this->_boController,"BoPopulate",array("Db" => ucfirst(strtolower($this->_db_alias)), "Table" => $this->_table)),true,array("authorized" => (SLS_BoRights::getAdminType() == "developer") ? "true" : "false"));
				$this->_xml->addFullTag("edit",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Modify".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Modify".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id" => ""),false) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("edit",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
				$this->_xml->addFullTag("clone",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Clone".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Clone".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id" => ""),false) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("clone",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
				$this->_xml->addFullTag("delete",($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"Delete".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table)))) ? $this->_generic->getFullPath($this->_boController,"Delete".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table),array("id" => ""),false) : "",true,array("authorized" => (SLS_BoRights::isAuthorized("delete",ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table))) ? "true" : "false"));
			$this->_xml->endTag("urls");
			$this->_xml->startTag("columns");
			foreach($this->_columns as $columnName => $infosColumn)
			{
				$this->_xml->startTag("column");
				foreach($infosColumn as $key => $value)
				{
					if (is_array($value) && !in_array($key,array("choices","values","errors")))
						continue;
						
					if ($key == "choices")
					{
						$this->_xml->startTag("choices");
						foreach($value as $currentValue)
							$this->_xml->addFullTag("choice",$currentValue,true);
						$this->_xml->endTag("choices");
					}
					else if ($key == "values")
					{
						$this->_xml->startTag("values");
						foreach($value as $currentLang => $values)
						{
							foreach($values as $currentValue)
							{
								if (is_array($currentValue))
									$this->_xml->addFullTag("value",$currentValue["value"],true,array("lang"=>$currentLang,"label"=>$currentValue["label"]));
								else
								{
									if ($this->_columns[$columnName]["specific_type"] == 'file' && $this->_columns[$columnName]["specific_type_extended"] == 'all')
									{
										$img = "false";
										if (file_exists($this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/".strtolower(str_replace("/","-",SLS_String::getExtensionMimeType(SLS_String::substrAfterLastDelimiter($currentValue,"."))).".png")))
										{
											$mime = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/".strtolower(str_replace("/","-",SLS_String::getExtensionMimeType(SLS_String::substrAfterLastDelimiter($currentValue,"."))).".png");
											if (SLS_String::startsWith(SLS_String::getExtensionMimeType(SLS_String::substrAfterLastDelimiter($currentValue,".")),"image/"))
												$img = "true";
										}
										else
											$mime = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/application-octet-stream.png";
										$this->_xml->addFullTag("value",$currentValue,true,array("lang"=>$currentLang,"img"=>$img,"mime"=>$mime));
									}
									else
										$this->_xml->addFullTag("value",$currentValue,true,array("lang"=>$currentLang));
								}
							}
						}
						$this->_xml->endTag("values");
					}
					else if ($key == "errors")
					{
						$this->_xml->startTag("errors");
						foreach($value as $currentLang => $currentValue)
						{
							$this->_xml->addFullTag("error",$infosColumn["label"]." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_'.strtoupper($currentValue)],true,array("lang"=>$currentLang));
							if (!in_array($currentLang,$langsError))
							{
								$this->pushNotif("error",sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_ERROR'],$currentLang));
								$langsError[] = $currentLang;
							}
						}
						$this->_xml->endTag("errors");
					}
					else
						$this->_xml->addFullTag($key,$value,true);
				}
				$this->_xml->endTag("column");
			}
			$this->_xml->endTag("columns");
			$this->_xml->startTag("bearers");
			foreach($this->_bearers as $bearerTable => $infosColumn)
			{
				$this->_xml->startTag("column");
				foreach($infosColumn as $key => $value)
				{
					if (is_array($value) && !in_array($key,array("choices","values")))
						continue;
						
					if ($key == "choices")
					{
						$this->_xml->startTag("choices");
						foreach($value as $currentValue)
							$this->_xml->addFullTag("choice",$currentValue,true);
						$this->_xml->endTag("choices");
					}
					else if ($key == "values")
					{
						$this->_xml->startTag("values");
						foreach($value as $currentValue => $currentValues)
							$this->_xml->addFullTag("value",$currentValues["value"],true,array("lang"=>$this->_defaultLang,"label"=>$currentValues["label"]));
						$this->_xml->endTag("values");
					}
					else
						$this->_xml->addFullTag($key,$value,true);
				}
				$this->_xml->endTag("column");
			}
			$this->_xml->endTag("bearers");
			$this->_xml->startTag("children");
			foreach($this->_children as $childTable => $childInfos)
			{
				$this->_xml->startTag("child");
					$this->_xml->startTag("model");
					foreach($childInfos["model"] as $key => $value)
						$this->_xml->addFullTag($key,$value,true);
					$this->_xml->endTag("model");
					$this->_xml->startTag("urls");
					foreach($childInfos["urls"] as $key => $value)
						$this->_xml->addFullTag($key,$value["url"],true,array("authorized" => $value["authorized"]));
					$this->_xml->endTag("urls");
					$this->_xml->startTag("columns");
					foreach($childInfos["columns"] as $columnName => $infosColumn)
					{
						$this->_xml->startTag("column");
						foreach($infosColumn as $key => $value)
						{
							if (is_array($value) && !in_array($key,array("choices","values","errors")))
								continue;
								
							if ($key == "choices")
							{
								$this->_xml->startTag("choices");
								foreach($value as $currentValue)
									$this->_xml->addFullTag("choice",$currentValue,true);
								$this->_xml->endTag("choices");
							}
							else if ($key == "values")
							{
								$this->_xml->startTag("values");
								foreach($value as $offset => $valuesChildren)
								{
									$this->_xml->startTag("record");
									foreach($valuesChildren as $currentLang => $values)
									{
										foreach($values as $currentValue)
										{
											if (is_array($currentValue))
												$this->_xml->addFullTag("value",$currentValue["value"],true,array("lang"=>$currentLang,"label"=>$currentValue["label"]));
											else
											{
												if ($this->_children[$childTable]["columns"][$columnName]["specific_type"] == 'file' && $this->_children[$childTable]["columns"][$columnName]["specific_type_extended"] == 'all')
												{
													$img = "false";
													if (file_exists($this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/".strtolower(str_replace("/","-",SLS_String::getExtensionMimeType(SLS_String::substrAfterLastDelimiter($currentValue,"."))).".png")))
													{
														$mime = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/".strtolower(str_replace("/","-",SLS_String::getExtensionMimeType(SLS_String::substrAfterLastDelimiter($currentValue,"."))).".png");
														if (SLS_String::startsWith(SLS_String::getExtensionMimeType(SLS_String::substrAfterLastDelimiter($currentValue,".")),"image/"))
															$img = "true";
													}
													else
														$mime = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/application-octet-stream.png";
													$this->_xml->addFullTag("value",$currentValue,true,array("lang"=>$currentLang,"img"=>$img,"mime"=>$mime));
												}
												else
													$this->_xml->addFullTag("value",$currentValue,true,array("lang"=>$currentLang));
											}
										}
									}
									$this->_xml->endTag("record");
								}
								$this->_xml->endTag("values");
							}
							else if ($key == "errors")
							{
								$this->_xml->startTag("errors");
								foreach($value as $currentLang => $currentValues)
								{
									$this->_xml->startTag("record");
									foreach($currentValues as $currentLang => $currentValue)
									{
										if (!empty($currentValue))
										{
											$this->_xml->addFullTag("error",$infosColumn["label"]." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_'.strtoupper($currentValue)],true,array("lang"=>$currentLang));
											if (!in_array($currentLang,$langsError))
											{
												$this->pushNotif("error",sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_ERROR'],$currentLang));
												$langsError[] = $currentLang;
											}
										}
										else
											$this->_xml->addFullTag("error","",true,array("lang"=>$currentLang));
									}
									$this->_xml->endTag("record");
								}
								$this->_xml->endTag("errors");
							}
							else
								$this->_xml->addFullTag($key,$value,true);
						}
						$this->_xml->endTag("column");
					}
					$this->_xml->endTag("columns");
					$this->_xml->startTag("bearers");
					foreach($this->_children[$childTable]["bearers"] as $bearerTable => $infosColumn)
					{
						$this->_xml->startTag("column");
						foreach($infosColumn as $key => $value)
						{
							if (is_array($value) && !in_array($key,array("choices","values")))
								continue;
								
							if ($key == "choices")
							{
								$this->_xml->startTag("choices");
								foreach($value as $currentValue)
									$this->_xml->addFullTag("choice",$currentValue,true);
								$this->_xml->endTag("choices");
							}
							else if ($key == "values")
							{
								$this->_xml->startTag("values");
								foreach($value as $childItem => $values)
								{
									$this->_xml->startTag("record");
									foreach($values as $currentValue => $currentValues)
										$this->_xml->addFullTag("value",$currentValues["value"],true,array("lang"=>$this->_defaultLang,"label"=>$currentValues["label"]));
									$this->_xml->endTag("record");
								}
								$this->_xml->endTag("values");
							}
							else
								$this->_xml->addFullTag($key,$value,true);
						}
						$this->_xml->endTag("column");
					}
					$this->_xml->endTag("bearers");
				$this->_xml->endTag("child");
			}
			$this->_xml->endTag("children");
		$this->_xml->endTag("page");
		# /Page infos
		
		return $this->_xml;
	}
}
?>