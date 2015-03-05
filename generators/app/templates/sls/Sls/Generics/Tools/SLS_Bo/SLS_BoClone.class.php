<?php
/**
 * SLS_BoClone Tool - Generate back-office cloning
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package SLS.Generics.Tools.SLS_Bo
 * @since 1.1
 */
class SLS_BoClone extends __SLS_Bo
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
		
		# Objects
		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($this->_db_alias)),"user");
		$this->_object = new $className();
		$this->_table = $this->_object->getTable();
		$this->_clone = new $className();
		$this->_columns = array();
		$this->_filters = array();
		$this->_types = array();
		# /Objects
				
		# Params
		$ids = $this->_http->getParam("id");
		$ids = (SLS_String::contains($ids,"|")) ? explode("|",$ids) : array($ids);
		# /Params
		
		# Types
		$types = $this->_db->showColumns($this->_table);
		for($i=0 ; $i<$count=count($types) ; $i++)
		{
			$nativeType = "text";
			switch($types[$i]->Type)
			{
				case (false !== $typeMatch = $this->containsRecursive($types[$i]->Type,array("int","float","double","decimal","real"))):
					$nativeType = "number";
					break;
				case (false !== $typeMatch = $this->containsRecursive($types[$i]->Type,array("year","datetime","timestamp","time","date"))):
					$nativeType = "date_".$typeMatch;
					break;
			}
			$this->_types[$types[$i]->Field] = $nativeType;
		}
		# /Types
		
		# Blocking specificities
		$specificities = $this->_xmlType->getTagsAttributes("//sls_configs/entry[@table='".$this->_db_alias."_".$this->_table."' and (@type='position' or @type='uniqid' or @type='email')]",array("column","type"));
		for($i=0 ; $i<$count=count($specificities) ; $i++)
		{	
			$column = $specificities[$i]["attributes"][0]["value"];
			$type = $specificities[$i]["attributes"][1]["value"];
			if (!array_key_exists($column,$this->_columns))
				$this->_columns[$column] = $type;
		}
		$filters = $this->_xmlFilter->getTags("//sls_configs/entry[@table='".$this->_db_alias."_".$this->_table."' and @filter='hash']/@column");
		# Blocking specificities
		
		# Perform clone
		if ($this->_object->isMultilanguage())
		{
			$siteLangs = $this->_lang->getSiteLangs();
			unset($siteLangs[array_search($this->_defaultLang,$siteLangs)]);
			array_unshift($siteLangs,$this->_defaultLang);
			$langs = $siteLangs;
		}
		else
			$langs =  array($this->_defaultLang);
		
		// Recordsets to clone
		$nbClone = 0;
		foreach($ids as $id)
		{
			// Next id
			$cloneId = $this->_object->giveNextId();
			
			// Each lang
			foreach($langs as $lang)
			{
				if ($this->_object->isMultilanguage())
					$this->_clone->setModelLanguage($lang);
					
				// Get recordset
				if ($this->_object->getModel($id) === true)
				{
					// Foreach column
					foreach($this->_object->getParams() as $key => $value)
					{
						if ($key == $this->_object->getPrimaryKey() || $key == "pk_lang")
							continue;
						
						// Setter
						$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$key)," ",false)),"");
						
						// Specific type ?
						if (array_key_exists($key,$this->_columns) && $this->_columns[$key] != "email")
						{
							// Default lang
							if ($lang == $this->_defaultLang && in_array($this->_columns[$key],array("uniqid","position")))
							{
								// Regenerate uniqid
								if ($this->_columns[$key] == "uniqid")
									$value = substr(md5(time().substr(sha1(microtime()),0,rand(12,25))),mt_rand(1,20),40);
								// Get next position
								else if ($this->_columns[$key] == "position")
								{
									$record = array_shift($this->_db->select("SELECT MAX(`".$key."`) AS max_position FROM `".$this->_table."` "));
									$value = (!empty($record->max_position) && is_numeric($record->max_position) && $record->max_position > 0) ? ($record->max_position+1) : 1;
								}
							}
							// Take the default lang value
							else
								$value = $this->_clone->__get($key);
						}
						
						// Set
						if (in_array($key,$filters))
							$this->_clone->__set($key,$value);
						else
							$this->_clone->$functionName($value);
						
						// Unique error ?
						if ($this->_clone->getError($key) == "E_UNIQUE")
						{	
							if (array_key_exists($key,$this->_columns) && $this->_columns[$key] == "email")
								$value = "clone_".time()."@".((substr_count($this->_generic->getSiteConfig("domainName"),".") > 1) ? SLS_String::substrAfterLastDelimiter(SLS_String::substrBeforeLastDelimiter($this->_generic->getSiteConfig("domainName"),"."),".").".".SLS_String::substrAfterLastDelimiter($this->_generic->getSiteConfig("domainName"),".") : $this->_generic->getSiteConfig("domainName"));
							else
							{
								switch($this->_types[$key])
								{
									case "number":
										$record = array_shift($this->_db->select("SELECT MAX(`".$key."`) AS max_nb FROM `".$this->_table."` "));
										$value = (!empty($record->max_nb) && is_numeric($record->max_nb)) ? ($record->max_nb+1) : 1;
										break;
									case (SLS_String::startsWith($this->_types[$key],"date_")):
										$record = array_shift($this->_db->select("SELECT MAX(`".$key."`) AS max_date FROM `".$this->_table."` "));
										$value = (!empty($record->max_date)) ? ($record->max_date) : "";
										$dateType = SLS_String::substrAfterFirstDelimiter($this->_types[$key],"date_");
										switch($dateType)
										{
											case (in_array($dateType,array("year","timestamp"))):
												$value = $value + 1;
												break;
											case "date":
												$value = SLS_Date::timestampToDate(strtotime("+ 1 second",SLS_Date::dateToTimestamp($value)));
												break;
											case "datetime":
												$value = SLS_Date::timestampToDateTime(strtotime("+ 1 second",SLS_Date::dateTimeToTimestamp($value)));
												break;
											case "time":
												$value = sls_string::substrAfterFirstDelimiter(SLS_Date::timestampToDateTime(strtotime("+ 1 second",SLS_Date::dateTimeToTimestamp(date("Y-m-d")." ".$value)))," ");
												break;
										}
										break;
									default:
										$value = substr(md5(time().substr(sha1(microtime()),0,rand(12,5))),mt_rand(1,5),10);
										break;
								}
							}
							
							$this->_clone->$functionName($value);
						}
					}
					
					$errors = $this->_clone->getErrors();
					
					if (empty($errors))
					{
						$this->_clone->create($cloneId);
						$nbClone += 1;
					}
				}
			}
			$this->_clone->clear();
		}
		if ($this->_object->isMultilanguage() && is_numeric($nbClone) && $nbClone > 0)
			$nbClone = floor($nbClone / count($langs));
		# Perform clone
		
		# Notif
		if (!empty($nbClone) && $nbClone !== false && is_numeric($nbClone))
			$this->pushNotif("success",($nbClone==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CLONE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CLONES'],$nbClone));
		else
			$this->pushNotif("error",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_ERROR_CLONE']);
		# /Notif
			
		if ($this->_async)
		{
			if ($nbClone !== false && is_numeric($nbClone) && $nbClone > 0)
			{
				$this->_render["status"] = "OK";
				$this->_render["result"]["message"] = ($nbClone==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CLONE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_CLONES'],$nbClone);
				$rememberList = (is_array($this->_session->getParam("SLS_BO_LIST"))) ? $this->_session->getParam("SLS_BO_LIST") : array();
				if (array_key_exists($this->_db_alias."_".$this->_table,$rememberList) && !empty($rememberList[$this->_db_alias."_".$this->_table]))
					$this->_render["forward"] = $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$rememberList[$this->_db_alias."_".$this->_table];
				else
					$this->_render["forward"] = $this->_generic->getFullPath($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
			}
			else
				$this->_render["result"]["message"] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_ASYNC_ERROR'];
			echo json_encode($this->_render);
			die();
		}
		else
		{	
			# Forward
			if ($this->_forward)
			{
				$rememberList = (is_array($this->_session->getParam("SLS_BO_LIST"))) ? $this->_session->getParam("SLS_BO_LIST") : array();
				if (array_key_exists($this->_db_alias."_".$this->_table,$rememberList) && !empty($rememberList[$this->_db_alias."_".$this->_table]))
					$this->_generic->redirect($rememberList[$this->_db_alias."_".$this->_table]);
				else
					$this->_generic->forward($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
			}
			# /Forward
		}
	}
}
?>