<?php
/**
 * SLS_Bo Tool - Father of SLS_Bo*.class.php
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package SLS.Generics.Tools.SLS_Bo
 * @since 1.01
 */
class __SLS_Bo
{
	public $_generic;
	public $_session;
	public $_http;
	public $_lang;
	public $_async = false;
	public $_render = array("status" => "ERROR",
							"logged" => "false",
							"expired" => "false",
							"authorized" => "false",
							"forward" => "",
							"errors" => array(),
							"result" => array());
	public $_publicActions = array("BoLogin",
								   "BoLogout",
								   "BoIsLogged", 
								   "BoForgottenPwd",
								   "BoMenu", 
								   "BoRenewPwd",
								   "BoSwitchLang");
	public $_autoActions = array("BoDashBoard", 
								 "BoDeleteFile", 
								 "BoFileUpload", 
								 "BoExport", 
								 "BoFkAc", 
								 "BoLike", 
								 "BoSetting",
								 "BoUnique", 
								 "BoUpload", 
								 "BoUploadProgress");
	public $_forbiddenActions = array("BoPopulate");
	public $_xmlRight;
	public $_xmlBo;
	public $_xmlBoColors;
	public $_defaultDb = null;
	public $_boController = "";
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct()
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_session = $this->_generic->getObjectSession();
		$this->_http = $this->_generic->getObjectHttpRequest();
		$this->_lang = $this->_generic->getObjectLang();
		$this->_async = (strtolower($this->_http->getParam("sls-request"))=="async") ? true : false;
		$this->_db = new SLS_Sql();
		$this->_boController = $this->_generic->getBo();
		$this->_boProtocol = $this->_generic->getControllersXML()->getTag("//controllers/controller[@name='".$this->_boController."']/@protocol");
		$this->_defaultDb = $this->_generic->getDbXML()->getTag("//dbs/db[@isDefault='true']/@alias");
		$this->_xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$this->_xmlRight = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml"));
		$this->_xmlType = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml"));
		$this->_xmlFilter = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml"));
		$this->_xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
		$this->_xmlBearer = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bearers.xml"));
		$this->_langs = $this->_lang->getSiteLangs();
		$this->_defaultLang = $this->_generic->getSiteConfig("defaultLang");
		
		$this->isAuthorized();
	}
	
	/**
	 * Generic getter
	 *
	 * @access public
	 * @param mixed $key
	 * @return value
	 * @since 1.1
	 */
	public function __get($key)
	{
		return $this->{$key} || null;
	}
	
	/**
	 * Check is current admin is authorized to access the current page
	 * 
	 */
	private function isAuthorized()
	{
		if (!in_array($this->_generic->getGenericScontrollerName(),$this->_publicActions))
		{
			// Logged
			if (SLS_BoRights::isLogged())
			{
				// Authorized
				if (SLS_BoRights::isAuthorized("","",$this->_generic->getActionId()))
				{
					if ($this->_async)
					{
						$this->_render["logged"] = "true";
						$this->_render["authorized"] = "true";
					}
				}
				// Not authorized
				else
				{
					if ($this->_async)
					{
						$this->_render["logged"] = "true";
						$this->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_AUTHORIZED'];
						echo json_encode($this->_render);
						die();
					}
					else
					{
						$this->_generic->forward($this->_boController,"BoDashBoard");
					}
				}
			}
			// Not logged
			else
			{
				if ($this->_async)
				{
					$this->_render["logged"] = "false";
					$this->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_LOGGED'];
					echo json_encode($this->_render);
					die();
				}
				else
				{
					$redirect = array("Redirect" => $this->_generic->getActionId());				    
				    $params = $this->_http->getParams();
				    unset($params["mode"]);
				    unset($params["smode"]);
				    $query = http_build_query($params,"","/");
				    $query = str_replace(array("%5B","%5D","=/","="),array("[","]","=|sls_empty|/","/"),preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query));
				    if (SLS_String::endsWith(trim($query),"/"))
						$query = SLS_String::substrBeforeLastDelimiter(trim($query),"/");
					if (!empty($query))
						$query = $query.((count(explode("/",$query))%2 != 0) ? "/sls_empty" : "");
					$query = str_replace("/","|",$query);									   
				    if (!empty($query))
					    $redirect["RedirectMore"] = trim($query);
					
					$this->_generic->forward($this->_boController,"BoLogin",$redirect);
				}
			}
		}
	}
	
	/**
	 * Add a new notification in session
	 * 
	 * @param string $type the type of notification (success|warning|error|information)
	 * @param string $msg the notification message
	 */
	public function pushNotif($type,$msg)
	{
		$notifications = $this->_session->getParam("SLS_BO_NOTIFICATIONS");
		if (empty($notifications))
			$notifications = array("success" 		=> array(),
								   "warning" 		=> array(),
								   "error" 			=> array(),
								   "information" 	=> array());
		
		if (!array_key_exists($type,$notifications))
			$notifications[$type] = array();
			
		$notifications[$type][] = $msg;
		
		$this->_session->setParam("SLS_BO_NOTIFICATIONS",$notifications);
	}
	
	/**
	 * Format the notifications
	 * 
	 * @param SLS_XMLToolbox $xml the XML object
	 * @return SLS_XMLToolbox the XML updated
	 */
	public function formatNotif($xml)
	{	
		$notifications = $this->_session->getParam("SLS_BO_NOTIFICATIONS");
		if (empty($notifications))
			$notifications = array("success" 		=> array(),
								   "warning" 		=> array(),
								   "error" 			=> array(),
								   "information" 	=> array());
			
		$xml->startTag("notifications");
		foreach($notifications as $type => $msgs)
		{
			$msgs = (is_array($msgs)) ? $msgs : array($msgs);
			foreach($msgs as $msg)
				$xml->addFullTag("notification",$msg,true,array("type"=>$type));
		}
		$xml->endTag("notifications");
		
		$this->_session->delParam("SLS_BO_NOTIFICATIONS");
		
		return $xml;
	}
	
	/**
	 * Contains recursive
	 * 
	 * @param string $hay the string in which you search
	 * @param mixed $needles the string or the array of occurences searched
	 * @return mixed false if not contain, else occurence found
	 */
	public function containsRecursive($hay,$needles)
	{
		if (is_array($needles))
		{
			foreach($needles as $needle)
			{				
				if (SLS_String::contains($hay,$needle))				
					return $needle;
			}
		}
		else
			return SLS_String::contains($hay,$needles);
			
		return false;
	}
	
	/**
	 * Order array langs to unshift default lang as first offset
	 * 
	 * @param string $a $key lang
	 * @param string $b $key lang
	 * @return int -1|0|1
	 */
	public function unshiftDefaultLang($a,$b)
	{
		if ($a == $this->_defaultLang)
			return -1;			
		if ($b == $this->_defaultLang)
			return 1;
		
		return ($a < $b) ? -1 : 1;
	}
	
	/**
	 * Get columns of a given table
	 * 
	 * @param string $db the db alias of the table
	 * @param string $table the wanted table you want to extract columns infos
	 * @param string $boPath xPath of the table in bo.xml
	 * @param string $classFather the father model (if children)
	 * @param bool $needPk if you want primary_key
	 * @param bool $needBoSettings if you want bo listing privileges (list|edit|filter)
	 * @return array $columns columns of the table
	 */
	public function getTableColumns($db,$table,$boPath="",$classFather="",$needPk=false,$needBoSettings=false)
	{
		$infosTable = $this->_db->showColumns($table);
		$isMultilanguage = false; 
		$uniquesMultilang = array();
		$columns = array();
		foreach((is_array($infosTable)) ? $infosTable : array($infosTable) as $infoTable)
		{
			if ($infoTable->Key == "PRI" && $infoTable->Field == "pk_lang")
			{
				$isMultilanguage = true;
				break;
			}
		}
		
		// Show create table
		if ($isMultilanguage)
		{
			$create = array_shift($this->_db->select("SHOW CREATE TABLE `".$table."`"));
			$instructions = array_map("trim",explode("\n",$create->{Create." ".Table}));						
			foreach($instructions as $instruction)
			{
				if (SLS_String::startsWith($instruction,"UNIQUE KEY"))
				{
					$uniqueColumns = explode(",",SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($instruction,"("),")"));
					if (count($uniqueColumns) == 2 && in_array("`pk_lang`",$uniqueColumns))
					{
						$uniqueColumn = array_shift($uniqueColumns);
						if ($uniqueColumn == "`pk_lang`")
							$uniqueColumn = array_shift($uniqueColumns);
							
						$uniquesMultilang[] = str_replace("`","",$uniqueColumn);
					}
				}
			}
		}
		
		// Get columns
		if (is_array($infosTable))
		{
			foreach($infosTable as $infoTable)
			{
				$pk = "false";
				
				// Switch primary keys
				if ($infoTable->Key == "PRI")
				{
					if (!$needPk)
						continue;
					else
						$pk = "true";
				}
				
				// Column
				$column = array("db" 						=> $db,
								"table" 					=> $table,
								"name" 						=> $infoTable->Field,
								"pk" 						=> $pk,
								"label" 					=> $infoTable->Field,
								"multilanguage" 			=> ($this->_object->isMultilanguage()) ? "true" : "false",
								"native_type" 				=> "string",
								"html_type" 				=> "input_text",
								"specific_type" 			=> "",
								"specific_type_extended" 	=> "",
								"file_uid"					=> uniqid(),
								"image_ratio" 				=> "*",
								"image_thumb"				=> "",
								"image_min_width" 			=> "*",
								"image_min_height" 			=> "*",
								"html" 						=> "false",
								"choices" 					=> array(),	
								"values" 					=> (empty($classFather)) ? $this->_langsValues : array(),
								"errors" 					=> array(),
								"required" 					=> "true",
								"unique" 					=> "false",				
								"default" 					=> "",
								"ac_db" 					=> "",
								"ac_entity" 				=> "",
								"ac_fk"						=> "",
								"ac_column" 				=> "",
								"ac_label" 					=> "",
								"ac_pattern" 				=> "",
								"ac_multiple" 				=> "",
								"min_length" 				=> "",
								"max_length" 				=> "",
								"filters" 					=> "");
				if ($needBoSettings)
				{
					$column["list"] = "true";
					$column["edit"] = "false";
					$column["filter"] = "true";
					
					if (!empty($boPath))
					{
						$columnBoAttributes = array_shift($this->_xmlBo->getTagsAttributes($boPath."/columns/column[@name='".$column["name"]."']",array("displayList","allowEdit","displayFilter")));
						if (!empty($columnBoAttributes))
						{
							$columnBoAttributesOptions 	= array("true","false");
							$column["list"] 			= (in_array($columnBoAttributes["attributes"][0]["value"],$columnBoAttributesOptions)) ? $columnBoAttributes["attributes"][0]["value"] : "true";
							$column["edit"] 			= (in_array($columnBoAttributes["attributes"][1]["value"],$columnBoAttributesOptions)) ? $columnBoAttributes["attributes"][1]["value"] : "false";
							$column["filter"] 			= (in_array($columnBoAttributes["attributes"][2]["value"],$columnBoAttributesOptions)) ? $columnBoAttributes["attributes"][2]["value"] : "true";
						}
					}	
				}
				
				// Comment
				$comment = empty($infoTable->Comment) ? $infoTable->Field : $infoTable->Comment;
				if (SLS_String::startsWith($comment,"sls:lang:"))
				{
					$key = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
					$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $infoTable->Field : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
				}
				if (!empty($comment))
					$column["label"] = $comment;
				
				// Native type, possible choices
				$nativeType = $infoTable->Type;
				$columnValues = array();
				switch($nativeType)
				{
					case (false !== $typeMatch = $this->containsRecursive($nativeType,array("int"))):
						$columnType = "int";
						$column["html_type"] = "input_number";
						break;
					case (false !== $typeMatch = $this->containsRecursive($nativeType,array("float","double","decimal","real"))):
						$columnType = "float";
						$column["html_type"] = "input_number";
						break;
					case (false !== $typeMatch = $this->containsRecursive($nativeType,array("year","datetime","timestamp","time","date"))):
						$columnType = ($typeMatch == "timestamp") ? "datetime" : $typeMatch;
						$column["html_type"] = "input_".$typeMatch;
						if ($infoTable->Null == "NO")
						{
							switch ($typeMatch)
							{
								case "year": $column["default"] = date("Y"); 			break;
								case "time": $column["default"] = date("H:i:s"); 		break;
								case "date": $column["default"] = date("Y-m-d"); 		break;
								default: 	 $column["default"] = date("Y-m-d H:i:s"); 	break;
							}
						}
						break;
					case (false !== $typeMatch = $this->containsRecursive($nativeType,array("enum","set"))):
						$columnType = "string";
						$column["html_type"] = ($typeMatch == "enum") ? "input_radio" : "input_checkbox";
						$columnValues = explode("','",SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($nativeType, "')"), "('"));
						break;
					case (false !== $typeMatch = $this->containsRecursive($nativeType,array("text"))):
						$columnType = "string";
						$column["html_type"] = "input_textarea";
						break;
					default:
						$columnType = "string";
						$column["html_type"] = "input_text";
						break;
				}
				$column["native_type"] = $columnType;
				$column["choices"] = $columnValues;
				
				// MaxLength
				if (SLS_String::contains($infoTable->Type,"(") && SLS_String::endsWith(trim($infoTable->Type),")"))
				{
					$maxLength = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($infoTable->Type,"("),")");
					$column["max_length"] = (is_numeric($maxLength) && $maxLength > 0) ? $maxLength : "";
				}
				
				// Nullable ? unique ? default value ?
				$column["required"] = ($infoTable->Null == "NO") ? "true" : "false";
				$column["unique"] = ($infoTable->Key == "UNI" || in_array($column["name"],$uniquesMultilang)) ? "true" : "false";
				if (empty($column["default"]))
					$column["default"] = (empty($infoTable->Default)) ? "" : $infoTable->Default;
				
				// Allow HTML & i18n
				if (!empty($boPath))
				{
					$columnBoAttributes = array_shift($this->_xmlBo->getTagsAttributes($boPath."/columns/column[@name='".$column["name"]."']",array("allowHtml","multilanguage")));				
					if (!empty($columnBoAttributes))
					{
						$allowHtml = $columnBoAttributes["attributes"][0]["value"];
						$isMultilang = $columnBoAttributes["attributes"][1]["value"];
						$column["html"] = ($allowHtml == "true") ? "true" : "false";
						$column["multilanguage"] = ($isMultilang == "true") ? "true" : "false";
					}
				}
				
				// Specific type & extended
				$typeExists = array_shift($this->_xmlType->getTagsAttributes("//sls_configs/entry[@table='".$this->_db_alias."_".$table."' and @column='".$column["name"]."']",array("type","rules","thumbs")));
				if (!empty($typeExists))
				{
					$specificType = $typeExists["attributes"][0]["value"];
					$specificRules = $typeExists["attributes"][1]["value"];
					$specificThumbs = unserialize(str_replace("||#||",'"',$typeExists["attributes"][2]["value"]));
					$specificTypeExtended = "";
					
					switch($specificType)
					{
						case "address": 	/* Nothing */ 		break;
						case "color": 		/* Nothing */ 		break;
						case "email": 		/* Nothing */ 		break;
						case "url": 		/* Nothing */ 		break;
						case "position":
							$record = array_shift($this->_db->select("SELECT MAX(`".$column["name"]."`) AS max_position FROM `".$table."` "));
							$column["default"] = (!empty($record->max_position) && is_numeric($record->max_position) && $record->max_position > 0) ? ($record->max_position+1) : 1;
							break;
						case "uniqid":
							// Generate uid
							$column["default"] = substr(md5(time().substr(sha1(microtime()),0,rand(12,25))),mt_rand(1,20),(!empty($column["max_length"])) ? $column["max_length"] : 40);
							break;
						case (SLS_String::startsWith($specificType,"num_")):
							// Get numeric settings
							$specificTypeExtended = SLS_String::substrAfterFirstDelimiter($specificType,"num_");
							$specificType = "numeric";
							break;
						case (SLS_String::startsWith($specificType,"ip_")):
							// Get IP settings
							$specificTypeExtended = SLS_String::substrAfterFirstDelimiter($specificType,"ip_");
							$specificType = "ip";
							$column["default"] = $_SERVER['REMOTE_ADDR'];
							break;
						case (SLS_String::startsWith($specificType,"file_")):
							// Get file settings
							$specificTypeExtended = SLS_String::substrAfterFirstDelimiter($specificType,"file_");
							$specificType = "file";
							$column["html_type"] = "input_file";
							if ($specificTypeExtended == "img")
							{	
								if (!empty($specificThumbs))
								{	
									usort($specificThumbs,array($this,'sortThumbsMin'));
									$thumb = array_shift($specificThumbs);
									if (!empty($thumb["suffix"]))
										$column["image_thumb"] =  $thumb["suffix"];
								}
								$column["image_ratio"] = SLS_String::substrBeforeFirstDelimiter($specificRules,"|");
								$column["image_min_width"] = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($specificRules,"|"),"|");
								$column["image_min_height"] = SLS_String::substrAfterLastDelimiter($specificRules,"|");
							}
							break;
						case ($specificType == "complexity" && (!empty($specificRules))):
							// Get complexity settings & minLength
							if (SLS_String::contains($specificRules,"min"))
							{
								$column["min_length"] = SLS_String::substrAfterFirstDelimiter($specificRules,"min");
								$specificRules = SLS_String::substrBeforeFirstDelimiter($specificRules,"min");
								if (SLS_String::endsWith($specificRules,"|"))
									$specificRules = SLS_String::substrBeforeLastDelimiter($specificRules,"|");
							}
							$specificTypeExtended = $specificRules;							
							break;
						default: 			$specificType = "";		break;
					}
					$column["specific_type"] 			= $specificType;
					$column["specific_type_extended"] 	= $specificTypeExtended;
				}
				
				if (!empty($column["default"]) && SLS_String::startsWith($column["html_type"],"input_file"))
					$column["default"] = (file_exists($this->_generic->getPathConfig("files").$column["default"])) ? SLS_String::getUrlFile($column["default"]) : "";
				
				// Filters
				$filters = $this->_xmlFilter->getTags("//sls_configs/entry[@table='".$this->_db_alias."_".$table."' and @column='".$column["name"]."']/@filter");
				for($i=0 ; $i<$count=count($filters) ; $i++)
				{
					if ($filters[$i] == "hash")
						$column["html_type"] = "input_password";
					else
						$column["filters"] .= (((!empty($column["filters"])) ? "|" : "").$filters[$i]);
				}
				
				// pk_lang
				if ($needPk && $isMultilanguage && $infoTable->Field == "pk_lang")
				{
					$column["html_type"] = "input_radio";
					$column["choices"] = $this->_lang->getSiteLangs();
				}
				
				// Fk
				$columnFk = array();
				$fkExists = array_shift($this->_xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$this->_db_alias."_".$table."' and @columnFk='".$column["name"]."']",array("tablePk","labelPk")));
				if (!empty($fkExists))
				{
					$tableAlias = $this->_db_alias;
					$tableFk = $table;
					$tablePk = $fkExists["attributes"][0]["value"];
					$labelPk = $fkExists["attributes"][1]["value"];
					
					$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($tablePk,"_"),$tableAlias,"user");
					$classFk = ucfirst($tableAlias)."_".SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($tablePk,"_"));
					$objectFk = new $classFk();
					$pk = $objectFk->getPrimaryKey();								
					$str = $labelPk;
					$labelPkReal = $labelPk;
					$params = $objectFk->getParams(true,true);								
					foreach($params as $key => $value)
					{
						if (is_array($value))
						{
							foreach($value as $key2 => $value2)
							{
								if (SLS_String::contains($str,$key2))
								{
									$this->_generic->useModel($key,$tableAlias,"user");
									$classFk2 = ucfirst($tableAlias)."_".SLS_String::tableToClass($key);
									$object2 = new $classFk2();
									$str = str_replace($key2,$object2->getColumnComment($key2),$str);
								}
							}
						}
						else
						{			
							if (SLS_String::contains($str,$key))
								$str = str_replace($key,$objectFk->getColumnComment($key),$str);
						}
					}
					$labelPk = $str;
										
					$column["html_type"] = "input_ac";
					$column["ac_db"] = strtolower($tableAlias);
					$column["ac_entity"] = strtolower($tableFk);
					$column["ac_fk"] = $tablePk;
					$column["ac_column"] = $column["name"];
					if (SLS_String::startsWith($labelPk,"sls:lang:"))
					{	
						$globalKey = strtoupper(SLS_String::substrAfterFirstDelimiter($labelPk,"sls:lang:"));
						$labelPk = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$globalKey])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$globalKey]) ? $labelPk : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$globalKey]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$globalKey];
					}
					$column["ac_label"] = $labelPk;
					$column["ac_pattern"] = $labelPkReal;
					$column["ac_multiple"] = "false";
					if ($column["required"] == "false")
						$column["default"] = "0";
				}
				else
					$tablePk = null;
				
				if (empty($classFather) || (!empty($classFather) && $classFather != ucfirst($tablePk)))
					$columns[$column["name"]] = $column;
			}
		}
		
		return $columns;
	}
	
	public function sortThumbsMin($a,$b)
	{
		$key = (!empty($a["width"])) ? "width" : "height";
		if ($a[$key] <= 50)
			return 1;
		if ($b[$key] <= 50)
			return -1;
		return ($a[$key] > $b[$key]);
	}
}
?>