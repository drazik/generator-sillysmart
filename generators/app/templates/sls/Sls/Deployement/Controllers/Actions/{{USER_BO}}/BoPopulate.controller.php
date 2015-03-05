<?php
/**
* Class BoPopulate into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoPopulate extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		// Params
		$this->_db_alias = strtolower($this->_http->getParam("Db"));
		$this->_table = $this->_http->getParam("Table");
		
		// Objects
		$this->_columns = array();
		$this->_nbRecordsets = 0;
		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($db)),"user");
		$object = new $className();
		$this->_domainVisuals = "http://www.sillysmart.org";
		try{
			$webService = json_decode(file_get_contents($this->_generic->getCoreXML('sls')->getTag("//sls_configs/slsnetwork")));
			$www = array_shift($webService->servers->www);
			if (SLS_String::isValidUrl($www->domain))
				$this->_domainVisuals = $www->domain;
		}
		catch (Exception $e){}
		
		// Fetch columns infos
		$this->getTableColumns($object, $this->_db_alias, $this->_table);
		$this->populateTable($object,$this->_columns[$this->_db_alias.".".$this->_table],mt_rand(1,5));

		# Notif
		if (!empty($this->_nbRecordsets) && $this->_nbRecordsets !== false && is_numeric($this->_nbRecordsets))
			$this->_bo->pushNotif("success",($this->_nbRecordsets==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_POPULATE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_POPULATES'],$this->_nbRecordsets));
		else
			$this->_bo->pushNotif("error",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_ERROR_POPULATE']);
		# /Notif
		
		# Forward
		if ($this->_bo->_async)
		{
			if (!empty($this->_nbRecordsets) && $this->_nbRecordsets !== false && is_numeric($this->_nbRecordsets))
			{
				$this->_bo->_render["status"] = "OK";
				$this->_bo->_render["result"]["message"] = ($this->_nbRecordsets==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_POPULATE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_POPULATES'],$this->_nbRecordsets);
				$rememberList = (is_array($this->_session->getParam("SLS_BO_LIST"))) ? $this->_session->getParam("SLS_BO_LIST") : array();
				if (array_key_exists($this->_db_alias."_".$this->_table,$rememberList) && !empty($rememberList[$this->_db_alias."_".$this->_table]))
					$this->_bo->_render["forward"] = $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$rememberList[$this->_db_alias."_".$this->_table];
				else
					$this->_bo->_render["forward"] = $this->_generic->getFullPath($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
			}
			else
			{
				$this->_bo->_render["result"]["message"] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_ERROR_POPULATE'];
			}
			echo json_encode($this->_bo->_render);
			die();
		}
		else
		{
			$rememberList = (is_array($this->_session->getParam("SLS_BO_LIST"))) ? $this->_session->getParam("SLS_BO_LIST") : array();
			if (array_key_exists($this->_db_alias."_".$this->_table,$rememberList) && !empty($rememberList[$this->_db_alias."_".$this->_table]))
				$this->_generic->redirect($rememberList[$this->_db_alias."_".$this->_table]);
			else
				$this->_generic->forward($this->_bo->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
		}
		# /Forward
	}
	
	public function after()
	{
		parent::after();
	}
	
	public function populateTable($object,$columns,$nb=5)
	{
		$recordsetIds = array();
		if ($object->isMultilanguage())
		{
			$siteLangs = $this->_lang->getSiteLangs();
			unset($siteLangs[array_search($this->_bo->_defaultLang,$siteLangs)]);
			array_unshift($siteLangs,$this->_bo->_defaultLang);
			$langs = $siteLangs;
		}
		else
			$langs =  array($this->_bo->_defaultLang);
		
		// N recordsets to create
		for($i=0 ; $i<$nb ; $i++)
			$this->_nbRecordsets += $this->populateRecordset($object,$columns,$langs,$nb);
	}
	
	public function populateRecordset($object,$columns,$langs,$nb=5)
	{
		$objectId = $object->giveNextId();
		$nbRecordsets = 0;
		
		// Foreach model lang
		foreach($langs as $lang)
		{
			if ($object->isMultilanguage())
				$object->setModelLanguage($lang);
			
			// Each columns
			foreach($columns as $column => $infos)
			{
				// Setter
				$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$column)," ",false)),"");
				$value = "";
								
				// If first lang or multilang column
				if ($lang == $this->_bo->_defaultLang || $infos["multilanguage"] == "true")
				{
					// Specific types
					switch($infos["specific_type"])
					{
						case "address":
							$addressNb = mt_rand(1,250);
							$addressStreet = $this->generateWord(mt_rand(5,16));
							$types = array("alley", "avenue", "boulevard", "highway", "lane", "road", "route", "street");
							$cities = array("amsterdam ", "andorra ", "athens ", "belgrade ", "berlin ", "bern ", "bratislava ", "brussels ", "bucharest ", "chisinau ", "copenhagen ", "dublin ", "helsinki ", "kiev ", "lisbon ", "ljubljana ", "london ", "luxembourg ", "madrid ", "minsk ", "monaco ", "moscow ", "nicosia ", "nuuk ", "oslo ", "paris ", "podgorica ", "prague ", "reykjavik ", "riga ", "rome ", "san marino ", "sarajevo ", "skopje ", "sofia ", "stockholm ", "tallinn ", "tirana ", "vaduz ", "valletta ", "vatican city ", "vienna ", "vilnius ", "warsaw ", "zagreb");
							$addressStreetType = $types[array_rand($types, 1)];
							$addressCity = $cities[array_rand($cities, 1)];
							$value = $addressNb." ".ucfirst($addressStreet)." ".ucfirst($addressStreetType).", ".ucfirst($addressCity);
							break;
						case "color":
							$value = sprintf('#%02X%02X%02X', mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
							break;
						case "email":
							$value = "random_sls_".time()."@".((substr_count($this->_generic->getSiteConfig("domainName"),".") > 1) ? SLS_String::substrAfterLastDelimiter(SLS_String::substrBeforeLastDelimiter($this->_generic->getSiteConfig("domainName"),"."),".").".".SLS_String::substrAfterLastDelimiter($this->_generic->getSiteConfig("domainName"),".") : $this->_generic->getSiteConfig("domainName"));
							break;
						case "position": 
							$record = array_shift($this->_bo->_db->select("SELECT MAX(`".$column."`) AS max_position FROM `".$object->getTable()."` "));
							$value = (!empty($record->max_position) && is_numeric($record->max_position) && $record->max_position > 0) ? ($record->max_position+1) : 1;
							break;
						case "url":
							$issues = array(1,3,4,5,6,7,8,9,10,11,12,13,14,15,17,19,20,21,25,28,29,30,31,32,34,36,37,38,40,47,48,49,50,51,53,55,56,142,143,144,149,150,151,152,153,154,156,157,158,159,160,161,163,181,183,184,185,186,187,188,190,191,193,194,195,196,197,198,202,203,204,205,206,207,208,210,211,212,214,215,216,217,218,219,225,229,247,248,249,262,263,264,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,401,402,403,404,409,411,412,413,414,415,416,417,418,419,420,421,422,423,424,425,426,427,442,443,444,445,446,448,453,454,455,456,457,458,459,460,461,462,463,464,482,490,493,494,495,512,514,522,535,556,557,558,560,562,563,564,565,566,567,568,569,577,584,632,656,685,688,691,692,694,696,697,712,713,714,715,741,743,752,753,764,799,808,809,810,811,813,814,818,819,820,821,822,823,824,825,843,844,845,846,847,851,854,858,859,860,861,862,863,864,866,867,868,869,870,872,881,882,883,884,885,887,889,890,8);
							$value = "http://redmine.sillysmart.org/issues/".$issues[array_rand($issues,1)];
							break;
						case "uniqid":
							$value = substr(md5(time().substr(sha1(microtime()),0,rand(12,25))),mt_rand(1,20),(!empty($infos["max_length"])) ? $infos["max_length"] : 40);
							break;
						case "numeric":
							switch ($infos["specific_type_extended"])
							{
								case "all": $value = mt_rand($infos["min_length"],$infos["max_length"]); break;
								case "gt": 	$value = mt_rand(1,$infos["max_length"]); 					 break;
								case "gte": $value = mt_rand(0,$infos["max_length"]); 					 break;
								case "lt": 	$value = mt_rand(-1,-(1 * $infos["max_length"])); 			 break;
								case "gt": 	$value = mt_rand(0,(-1 * $infos["max_length"])); 			 break;
							}
							break;
						case "ip":
							if ($infos["specific_type_extended"] == "v4")
								$value = mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255);
							else
								$value = implode(':', str_split(md5(rand()), 4));
							break;
						case "file":
							if ($infos["specific_type_extended"] == "img")
							{
								$formats = array("square","land","portrait");
								$format = "";
								if ($infos["image_ratio"] != '*' && is_numeric($infos["image_ratio"]) && $infos["image_ratio"] > 0)
								{	
									switch ($infos["image_ratio"])
									{
										case ($infos["image_ratio"] > 1): 	$format = "land"; 		break;
										case ($infos["image_ratio"] < 1): 	$format = "portrait"; 	break;
										default: 							$format = "square"; 	break;
									}
								}
								if (empty($format))
									$format = $formats[array_rand($formats,1)];
								try{
									$fileNameGet = $this->_domainVisuals."/Public/Files/bo_populate_visuals/".$format."_".mt_rand(0,5).".jpg";
									$file = SLS_String::substrAfterLastDelimiter($fileNameGet,"/");
									
									if (!file_exists($this->_generic->getPathConfig("files")."__Uploads"))
										@mkdir($this->_generic->getPathConfig("files")."__Uploads");
									if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated"))
										@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated");
									$fileNamePut = $this->_generic->getPathConfig("files")."__Uploads/__Deprecated"."/".SLS_String::sanitize(SLS_String::substrBeforeLastDelimiter($file,"."),"_")."_".substr(md5(time().substr(sha1(microtime()),0,rand(5,12))),mt_rand(1,20),10).((SLS_String::contains($file,".")) ? ".".SLS_String::substrAfterLastDelimiter($file,".") : "");
									file_put_contents($fileNamePut,file_get_contents($fileNameGet));
									$image = new SLS_Image($fileNamePut);
									
									if (file_exists($fileNamePut))
									{
										$imageWidth = $image->getParam("width");
										$imageHeight = $image->getParam("height");
										$imageRatio = round($imageWidth / $imageHeight,2);
										$imageFinalRatio = $infos["image_ratio"];
										$size = array("x" => "0",
													  "y" => "0",
													  "w" => $imageWidth,
													  "h" => $imageHeight);
																				
										if ($imageFinalRatio != 1)
										{
											if ($imageRatio < $imageFinalRatio)											
												$size["h"] = round((1 / $imageFinalRatio) * $imageWidth,0);
											else
												$size["w"] = round($imageHeight * $imageFinalRatio,0);
										}
										
										$value = array("data" => array("name" 		=> $file,
																	   "tmp_name" 	=> $fileNamePut,
																	   "type" 		=> "image/jpeg",
																	   "size" 		=> filesize($fileNamePut),
																	   "error" 		=> "0"),
													   "size" => $size);
									}
								}
								catch (Exception $e){}
							}
							else
							{
								$fileExtensions = array("xlsx", "csv", "docx", "pdf", "txt", "eps");
								$fileNameGet = $this->_domainVisuals."/Public/Files/bo_populate_visuals/sls_file.".$fileExtensions[array_rand($fileExtensions,1)];
								$file = SLS_String::substrAfterLastDelimiter($fileNameGet,"/");
								if (!file_exists($this->_generic->getPathConfig("files")."__Uploads"))
									@mkdir($this->_generic->getPathConfig("files")."__Uploads");
								if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated"))
									@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated");
								$fileNamePut = $this->_generic->getPathConfig("files")."__Uploads/__Deprecated"."/".SLS_String::sanitize(SLS_String::substrBeforeLastDelimiter($file,"."),"_")."_".substr(md5(time().substr(sha1(microtime()),0,rand(5,12))),mt_rand(1,20),10).((SLS_String::contains($file,".")) ? ".".SLS_String::substrAfterLastDelimiter($file,".") : "");
								file_put_contents($fileNamePut,file_get_contents($fileNameGet));
								if (file_exists($fileNamePut))
								{
									$value = array("name" 		=> $file,
												   "tmp_name" 	=> $fileNamePut,
												   "type" 		=> "image/jpeg",
												   "size" 		=> filesize($fileNamePut),
												   "error" 		=> "0");
								}
							}
							break;
						case "complexity":
							$value = $this->generateWord(mt_rand($infos["min_length"],$infos["max_length"]-$infos["min_length"]),true);
							break;
					}
				}
				else
					$value = $object->__get($column);
				
				// No value set yet
				if (empty($value))
				{
					// Type date
					if (in_array($infos["native_type"],array("date","time","year","timestamp","datetime")))
					{
						switch ($typeMatch)
						{
							case "year": $value = date("Y"); 			break;
							case "time": $value = date("H:i:s"); 		break;
							case "date": $value = date("Y-m-d"); 		break;
							default: 	 $value = date("Y-m-d H:i:s"); 	break;
						}
					}
					// Type float
					if ($infos["native_type"] == "float")
					{
						$value = mt_rand(0,999).".".mt_rand(0,99);
					}
					// Type int
					if ($infos["native_type"] == "int")
					{
						$value = mt_rand(0,$infos["max_length"]);
					}
					// Type string
					if ($infos["native_type"] == "string")
					{
						// Enum || set
						if (in_array($infos["mysql_type"],array("enum","set")))
						{	
							if ($infos["mysql_type"] == "enum")
								$value = $infos["choices"][array_rand($infos["choices"],1)];
							else
							{
								$offsets = array_rand($infos["choices"],mt_rand(1,(count($infos["choices"])-1)));
								$value = array();
								if (is_array($offsets))
									foreach($offsets as $offset)
										$value[] = $infos["choices"][$offset];
								else
									$value[] = $infos["choices"][$offsets];
								$value = implode(",",$value);
							}
						}
						// Pure txt
						else
						{
							if ($infos["max_length"] > 1000)
							{
								$value = "";
								for($i=0 ; $i<10 ; $i++)
									$value .= wordwrap($this->generateWord(mt_rand(80,100)),mt_rand(8,16)," ",true).(($i==9) ? "": "\n");
								/*
								try {
									$value = simplexml_load_file("http://www.lipsum.com/feed/xml?amount=10&what=paras")->lipsum->__toString();
								} catch (Exception $e){
									$value = wordwrap($this->generateWord(80),6," ")."\n".wordwrap($this->generateWord(80),7," ");
								}
								*/
								if ($infos["html"] == "true")
									$value = implode("",array_map(array($this,'embedHtmlP'),explode("\n",$value)));
							}
							else
							{
								$value = "";
								$loop = round(($infos["max_length"]/100),0);
								for($i=0 ; $i<$loop ; $i++)
									$value .= wordwrap($this->generateWord(mt_rand(80,100)),mt_rand(8,16)," ",true).(($i==($loop-1)) ? "": "\n");
								$value = wordwrap($this->generateWord(80),6," ",true)."\n".wordwrap($this->generateWord(80),7," ",true);
								/*
								try {
									$value = simplexml_load_file("http://www.lipsum.com/feed/xml?amount=50&what=words")->lipsum->__toString();
								} catch (Exception $e){
									$value = wordwrap($this->generateWord(80),6," ")."\n".wordwrap($this->generateWord(80),7," ");
								}
								*/
							}
						}
					}
					
					// Fk
					if (!empty($infos["fk"]))
					{
						$fkDb = SLS_String::substrBeforeFirstDelimiter($infos["fk"],"_");
						$fkModel = SLS_String::substrAfterFirstDelimiter($infos["fk"],"_");
						$fkClass = ucfirst(strtolower($fkDb))."_".$fkModel;
						$this->_generic->useModel($fkModel,$fkDb,"user");
						$fkObject = new $fkClass();
						$sql = "SELECT COUNT(*) AS total FROM `".$fkObject->getTable()."` ";
						if ($fkObject->isMultilanguage())
							$sql .= "WHERE pk_lang = ".$this->_bo->_db->quote($lang)." ";
						$resultNbRecordsets = array_shift($this->_bo->_db->select($sql));
						
						// Populate fk
						if ($resultNbRecordsets->total < $nb)
						{
							$this->getTableColumns($fkObject, $fkDb, $fkObject->getTable());
							$nbRecordsets += $this->populateTable($fkObject,$this->_columns[$fkDb.".".$fkObject->getTable()]);
						}
						
						$sql = "SELECT `".$fkObject->getPrimaryKey()."` AS pk FROM `".$fkObject->getTable()."` ";
						if ($fkObject->isMultilanguage())
							$sql .= "WHERE pk_lang = ".$this->_bo->_db->quote($lang)." ";
						$sql .= "ORDER BY rand() LIMIT 1 ";
						$resultRecordset = array_shift($this->_bo->_db->select($sql));
						$value = $resultRecordset->pk;
					}
				}
					
				// Max-length
				if (is_string($value) && !empty($infos["max_length"]) && mb_strlen($value,"UTF-8") > $infos["max_length"])
					$value = mb_substr($value,0,$infos["max_length"],"UTF-8");
					
				$object->$functionName($value);
			}
			
			$errors = $object->getErrors();
			if (empty($errors))
				$object->create($objectId);
		}
		if (empty($errors))
			$nbRecordsets++;
			
		$object->clear();
		
		return $nbRecordsets;
	}
	
	/**
	 * Fetch table infos
	 * 
	 * @param string $db alias of database
	 * @param string $table the table to fetch columns
	 */
	public function getTableColumns($object,$db,$table)
	{	
		// Objects
		$className = ucfirst(strtolower($db))."_".SLS_String::tableToClass($table);
		$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($className)."']";
		$boExists = $this->_bo->_xmlBo->getTag($boPath."/@type");
		if (empty($boExists))		
			$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($className)."']";
		if ($db != $this->_bo->_db->getCurrentDb())
			$this->_bo->_db->changeDb($db);
		$infosTable = $this->_bo->_db->showColumns($table);
		if (!array_key_exists($db.".".$table,$this->_columns))
			$this->_columns[$db.".".$table] = array();
		$uniquesMultilang = array();
		
		// Show create table
		if ($object->isMultilanguage())
		{
			$create = array_shift($this->_bo->_db->select("SHOW CREATE TABLE `".$table."`"));
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
				// Switch primary keys
				if ($infoTable->Key == "PRI")
					continue;
				
				// Objects
				$column = array("native_type" 				=> "string",
								"mysql_type"				=> "varchar",
								"specific_type" 			=> "",
								"specific_type_extended" 	=> "",
								"min_length" 				=> "0",
								"max_length" 				=> "255",
								"multilanguage" 			=> "false",
								"unique" 					=> "false",
								"html" 						=> "false",
								"choices" 					=> array(),
								"required" 					=> "false",
								"unique" 					=> "false",				
								"default" 					=> "",
								"fk" 						=> "",
								"image_ratio" 				=> "*",
								"image_min_width" 			=> "*",
								"image_min_height" 			=> "*");
				$columnValues = array();
				$maxLengthSet = false;
								
				// MaxLength
				if (SLS_String::contains($infoTable->Type,"(") && SLS_String::endsWith(trim($infoTable->Type),")"))
				{
					$maxLength = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($infoTable->Type,"("),")");
					$column["max_length"] = (is_numeric($maxLength) && $maxLength > 0) ? $maxLength : "";
					$maxLengthSet = true;
				}
				
				// Native type, possible choices
				$nativeType = $infoTable->Type;
				switch($nativeType)
				{
					case (false !== $typeMatch = $this->_bo->containsRecursive($nativeType,array("int"))):
						if (!$maxLengthSet)
						{
							switch ($typeMatch)
							{								
								case "tinyint": 	$column["max_length"] = "255"; 					break;
								case "smallint": 	$column["max_length"] = "65535"; 				break;
								case "mediumint": 	$column["max_length"] = "16777215"; 			break;
								case "int": 		$column["max_length"] = "4294967295"; 			break;
								case "integer": 	$column["max_length"] = "4294967295"; 			break;
								case "bigint": 		$column["max_length"] = "18446744073709551615"; break;
							}
						}
						$column["native_type"] = "int";
						$column["mysql_type"] = $typeMatch;
						break;
					case (false !== $typeMatch = $this->_bo->containsRecursive($nativeType,array("float","double","decimal","real"))):
						if (!$maxLengthSet)
							$column["max_length"] = "24";
						$column["native_type"] = "float";
						$column["mysql_type"] = $typeMatch;
						break;
					case (false !== $typeMatch = $this->_bo->containsRecursive($nativeType,array("year","datetime","timestamp","time","date"))):
						$typeMatch = ($typeMatch == "timestamp") ? "datetime" : $typeMatch;
						$column["native_type"] = $typeMatch;
						$column["mysql_type"] = $typeMatch;
						break;
					case (false !== $typeMatch = $this->_bo->containsRecursive($nativeType,array("enum","set"))):
						$column["native_type"] = "string";
						$column["mysql_type"] = $typeMatch;
						$columnValues = explode("','",SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($nativeType, "')"), "('"));
						break;
					case (false !== $typeMatch = $this->_bo->containsRecursive($nativeType,array("text","char"))):
						if (!$maxLengthSet)
						{
							switch ($typeMatch)
							{
								case "char": 											 $column["max_length"] = "1"; 			break;
								case (in_array($typeMatch,array("varchar","tinytext"))): $column["max_length"] = "255"; 		break;
								case "text": 											 $column["max_length"] = "65535"; 		break;
								case "mediumtext": 										 $column["max_length"] = "16777215"; 	break;
								case "longtext": 										 $column["max_length"] = "4294967295"; 	break;
							}
						}
						$column["native_type"] = "string";
						$column["mysql_type"] = $typeMatch;
						break;
				}
				
				$column["choices"] = $columnValues;
				
				// Nullable ? unique ? default value ?
				$column["required"] = ($infoTable->Null == "NO") ? "true" : "false";
				$column["unique"] = ($infoTable->Key == "UNI" || in_array($infoTable->Field,$uniquesMultilang)) ? "true" : "false";
				if (empty($column["default"]))
					$column["default"] = (empty($infoTable->Default)) ? "" : $infoTable->Default;
				
				// Allow HTML & i18n
				$columnBoAttributes = array_shift($this->_bo->_xmlBo->getTagsAttributes($boPath."/columns/column[@name='".$infoTable->Field."']",array("allowHtml","multilanguage")));
				if (!empty($columnBoAttributes))
				{
					$allowHtml = $columnBoAttributes["attributes"][0]["value"];
					$isMultilang = $columnBoAttributes["attributes"][1]["value"];
					$column["html"] = ($allowHtml == "true") ? "true" : "false";
					$column["multilanguage"] = ($isMultilang == "true") ? "true" : "false";
				}
				
				// Specific type & extended
				$typeExists = array_shift($this->_bo->_xmlType->getTagsAttributes("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$infoTable->Field."']",array("type","rules")));
				if (!empty($typeExists))
				{
					$specificType = $typeExists["attributes"][0]["value"];
					$specificRules = $typeExists["attributes"][1]["value"];
					$specificTypeExtended = "";
					
					switch($specificType)
					{
						case "address": 	/* Nothing */ 		break;
						case "color": 		/* Nothing */ 		break;
						case "email": 		/* Nothing */ 		break;
						case "url": 		/* Nothing */ 		break;
						case "position": 
							$column["unique"] = "true";
							break;
						case "uniqid":
							$column["unique"] = "true";
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
								$minLengthSet = true;
							}
							$specificTypeExtended = $specificRules;							
							break;
						default: 			$specificType = "";		break;
					}
					$column["specific_type"] 			= $specificType;
					$column["specific_type_extended"] 	= $specificTypeExtended;
				}
				
				// Fk
				$columnFk = array();
				$fkExists = array_shift($this->_bo->_xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$db."_".$table."' and @columnFk='".$infoTable->Field."']",array("tablePk","labelPk")));
				if (!empty($fkExists))
					$column["fk"] = $fkExists["attributes"][0]["value"];
				
				$this->_columns[$db.".".$table][$infoTable->Field] = $column;
			}
		}
	}
	
	/**
	 * Generate word
	 * 
	 * @param int $length
	 * @return string $word
	 */
	public function generateWord($length = 8, $complex=false) 
	{
	    $chars = 'abcdefghijklmnopqrstuvwxyz';
	    if ($complex)
	    	$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789,?.:/!*#{([|_-\)]+=}';
	    $count = mb_strlen($chars);
	
	    for ($i = 0, $word = ''; $i < $length; $i++) 
	    {
	        $index = rand(0, $count - 1);
	        $word .= mb_substr($chars, $index, 1);
	    }
	
	    return $word;
	}
	
	/**
	 * Embed by an html paragraph
	 * 
	 * @param string $a
	 * @return string
	 */
	public function embedHtmlP($a)
	{
		return "<p>".$a."</p>";
	}
}
?>