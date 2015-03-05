<?php
/**
 * Mother class of models
 * 
 * @author Florian Collot
 * @author Laurent Bientz
 * @copyright SillySmart
 * @package Sls.Models.Core 
 * @see SLS_FrontmodelSql
 * @since 1.0  
 */
class SLS_FrontModel
{	
	// Class variables	 
	protected $_update_array = array();
	protected $_generic;
	protected $_sql;
	protected $_table;
	protected $_db;
	protected $_primaryKey;
	protected $_fks;
	protected $_isMultilanguage = false;
	protected $_modelLanguage = "";
		 
	/**
	 * Constructor
	 *
	 * @access public
	 * @param bool $multilanguage true if we have multilanguage content, else false
	 * @since 1.0
	 */
	public function __construct($multilanguage=false)
	{
		$this->_isMultilanguage = ($multilanguage) ? true : false;
		
		$this->beSurInitDbInfos();		
		$this->_generic = SLS_Generic::getInstance();
		$sql = get_class($this)."Sql";
		$this->_sql = new ${sql}($this->_table, $this->_primaryKey, $this->_isMultilanguage);
	}
	
	/**
	 * Set the current language of the model
	 *
	 * @access public
	 * @param string $lang the current language of the model (default lang if not filled)
	 * @since 1.0
	 */
	public function setModelLanguage($lang="")
	{
		if ($this->_isMultilanguage)
		{
			$this->_sql->setModelLanguage($lang);
			$this->_modelLanguage = (empty($lang)) ? $this->_generic->getObjectLang()->getLang() : $lang;			
			return true;
		}
		else 
			return false;
	}
	
	/**
	 * Get an object model
	 *
	 * @access public
	 * @param int $id the id of the model (PK)
	 * @return bool true if ok, else false
	 * @since 1.0
	 */
	public function getModel($id)
	{		
		if ($this->_isMultilanguage && empty($this->_modelLanguage))			
			$this->setModelLanguage();
		
		$object = $this->_sql->getModel($id);
		
		if (!is_numeric($id))
			return false;
		if (!is_object($object))
			return false;
		
		foreach ($object as $key => $value)	{
			if (property_exists($this, "__".$key))					
				$this->{__.$key} = (is_null($value)) ? "" : $value;
		}
		return true;
	}
		
	/**
	 * Create child model
	 * 
	 * @access public
	 * @param int $pkMultiLang one of the PKs described the entity in the case of multilanguage content (empty by default)
	 * @return int id of the recordset
	 * @see SLS_FrontModel::save
	 * @see SLS_FrontModel::delete
	 * @since 1.0
	 */
	public function create($pkMultiLang="") 
	{
		if (!empty($pkMultiLang))
		{
			if ($this->_isMultilanguage && empty($this->_modelLanguage))
				$this->setModelLanguage();
						
			$this->{__.$this->_primaryKey} = $pkMultiLang;
		}
		
		$this->{__.$this->_primaryKey} = $this->_sql->create($this->getParams(),$this->{__.$this->_primaryKey});
			
		$this->_update_array = array();
		
		return $this->{__.$this->_primaryKey};
	}
	
	/**
	 * Delete child model
	 *
	 * @access public
	 * @param bool $allLangs true if you want to delete current recordset in all languages, else only the current lang
	 * @return mixed nb recordsets deleted if success, else false
	 * @see SLS_FrontModel::create
	 * @see SLS_FrontModel::save
	 * @since 1.0
	 */
	public function delete($allLangs=false) 
	{
		if ($this->_isMultilanguage && empty($this->_modelLanguage))		
			$this->setModelLanguage();
		if (!is_numeric($this->{__.$this->_primaryKey}))
			return false;
		
		// Move in deprecated specific files linked to the current model 
		$this->deleteFiles();
		
		// Check actions on delete on all foreign keys of the current model
		$this->onDeleteConstraints();
		
		return $this->_sql->delete($this->{__.$this->_primaryKey},$allLangs);
	}
	
	/**
	 * Check all the on delete constraints on all foreign keys of the current model
	 * 
	 * @access public
	 * @since 1.0.9
	 */
	public function onDeleteConstraints()
	{
		// Check fks
		$db = SLS_Sql::getInstance();
		$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));				
		$fks = $xmlFk->getTagsAttributes("//sls_configs/entry[@tablePk='".strtolower($this->getDatabase())."_".SLS_String::tableToClass($this->getTable())."' and @ondelete != 'no_action']",array("tableFk","columnFk","ondelete"));
		for($i=0 ; $i<$count=count($fks) ; $i++)
		{
			if (empty($fks) || (!empty($fks) && count($fks[0]["attributes"]) != 3))
				continue;
			
			$className = ucfirst(SLS_String::substrBeforeFirstDelimiter($fks[$i]["attributes"][0]["value"],"_"))."_".SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($fks[$i]["attributes"][0]["value"],"_"));
			$fk = $fks[$i]["attributes"][1]["value"];
			$onDelete = $fks[$i]["attributes"][2]["value"];			
			$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($className,"_"),SLS_String::substrBeforeFirstDelimiter($className,"_"),"user");							
			$object = new $className();			
			if ($object->getDatabase() != $db->getCurrentDb())
				$db->changeDb($object->getDatabase());
			
			switch ($onDelete)
			{
				case "set_null":					
					$db->update("UPDATE `".$object->getTable()."` SET `".$fk."` = NULL WHERE `".$fk."` = ".$db->quote($this->{__.$this->getPrimaryKey()})." ");
					break;
				case "cascade":					
					$objects = $db->select("SELECT * FROM `".$object->getTable()."` WHERE `".$fk."` = ".$db->quote($this->{__.$this->getPrimaryKey()})." ");
					for($j=0 ; $j<$countJ=count($objects) ; $j++)					
						if ($object->getModel($objects[$j]->{$object->getPrimaryKey()}) === true)
							$object->delete($object->isMultilanguage());
					break;
			}
		}
	}
	
	/**
	 * Delete files on columns for current tables
	 * 
	 * @access public
	 * @param array $columns columns on which you want to delete specific files (all columns of table if empty)
	 * @since 1.0.8
	 */
	public function deleteFiles($columns=array())
	{
		// Check type files		
		$xmlType = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml"));		
		$results = $xmlType->getTagsAttribute("//sls_configs/entry[@table='".$this->getDatabase()."_".$this->getTable()."' and (@type='file_all' or @type='file_img')]","column");
		
		if (empty($columns))
			$columns = $this->getColumns();
		
		if (!empty($results))
		{
			foreach($this->getParams() as $column => $value)
			{
				if (!in_array($column,$columns))
					continue;
				
				$result = array_shift($xmlType->getTagsAttributes("//sls_configs/entry[@table='".$this->getDatabase()."_".$this->getTable()."' and @column='".$column."' and (@type='file_all' or @type='file_img')]",array("type","thumbs")));
				if (!empty($result) && $this->getColumnDefault($column) != $value)
				{					
					$clone = $this->countModels($this->getTable(),array(),array(array("column"=>$column,"value"=>$value,"mode"=>"equal")));
					$type = $result["attributes"][0]["value"];
					
					if (file_exists($this->_generic->getPathConfig("files").$value) && !is_dir($this->_generic->getPathConfig("files").$value) && $clone < 2)
					{
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads"))
							@mkdir($this->_generic->getPathConfig("files")."__Uploads");
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated"))
							@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated");
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".$this->getTable()))
							@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".$this->getTable());
						
						if ($type == "file_all")
						{
							try{
								@rename($this->_generic->getPathConfig("files").$value,$this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".$value);
							}
							catch (Exception $e){}
						}
						else
						{
							$thumbs = unserialize(str_replace("||#||",'"',$result["attributes"][1]["value"]));
							@rename($this->_generic->getPathConfig("files").$value,$this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".$value);
							$baseName = $this->_generic->getPathConfig("files").SLS_String::substrBeforeLastDelimiter($value, ".".pathinfo($value,PATHINFO_EXTENSION));
							$baseExtension = pathinfo($value,PATHINFO_EXTENSION);
							
							foreach($thumbs as $thumb)
							{
								if (file_exists($baseName.$thumb["suffix"].".".$baseExtension) && !is_dir($baseName.$thumb["suffix"].".".$baseExtension))
									@rename($baseName.$thumb["suffix"].".".$baseExtension,$this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".SLS_String::substrBeforeLastDelimiter($value, ".".pathinfo($value,PATHINFO_EXTENSION)).$thumb["suffix"].".".pathinfo($value,PATHINFO_EXTENSION));
							}
						}
					} 
				}
			}
		}
	}
	
	/**
	 * The current child model is multilanguage ?
	 *
	 * @access public
	 * @return bool true if yes, else false
	 * @since 1.0
	 */
	public function isMultilanguage()
	{
		return $this->_isMultilanguage;
	}
	
	/**
	 * Getter of the database of the current table
	 *
	 * @access public
	 * @return string $db the database alias
	 * @since 1.0
	 * @example 
	 * // if the current database alias named "main"
	 * var_dump($model->getDatabase());
	 * // will produce : "main"
	 */
	public function getDatabase()
	{
		return $this->_db;
	}
	
	/**
	 * Getter of the Primary Key of the current table
	 *
	 * @access public
	 * @return string $pk the primary key
	 * @since 1.0
	 * @example 
	 * // if the current model has a primary key named "user_id"
	 * var_dump($object->getPrimaryKey());
	 * // will produce : "user_id"
	 */
	public function getPrimaryKey()
	{
		return $this->_primaryKey;
	}
	
	/**
	 * Getter of the current table
	 *
	 * @access public
	 * @return string $table the table name
	 * @since 1.0
	 * @example 
	 * // if the current model is map on a table named "user"
	 * var_dump($object->getTable());
	 * // will produce : "user"
	 */
	public function getTable()
	{
		return $this->_table;
	}
	
	/**
	 * Get all fks of the current model
	 * 
	 * @access public
	 * @return array $fks all fks name
	 * @since 1.1
	 */
	public function getFks()
	{
		return $this->_fks;
	}
	
	/**
	 * Getter of the errors of the current model
	 *
	 * @access public
	 * @return array $typeErrors all the errors
	 * @since 1.0
	 */
	public function getErrors()
	{
		return $this->_typeErrors;
	}
	
	/**
	 * Getter of one error 
	 *
	 * @access public
	 * @param string $key the property wanted
	 * @return string the error wanted
	 * @since 1.0
	 */
	public function getError($key)
	{
		return (isset($this->_typeErrors[$key])) ? $this->_typeErrors[$key] : "";
	}
	
	/**
	 * Flush an error
	 * 
	 * @access public
	 * @param string $key the property wanted
	 * @return bool true if flushed, else false
	 * @since 1.1
	 */
	public function flushError($key)
	{
		if (isset($this->_typeErrors[$key]))
		{
			unset($this->_typeErrors[$key]);
			return true;
		}
		return false;
	}
	
	/**
	 * Flush all errors
	 * 
	 * @access public
	 * @return bool true if flushed, else false
	 * @since 1.1
	 */
	public function flushErrors()
	{
		if (isset($this->_typeErrors))
		{
			$this->_typeErrors = array();
			return true;
		}
		return false;
	}
	
	/**
	 * Generic getter
	 *
	 * @access public
	 * @param string $var wanted class variable 
	 * @return string $value value of the wanted class variable
	 * @since 1.0
	 */
	public function __get($var) 
	{
		if (array_key_exists($var, $this->getParams()))
			return $this->{__.$var};
		else
			SLS_Tracing::addTrace(new Exception("Error: Class variable `".$var."` doesn't exists in this child model in get context"),true);
	}
	
	/**
	 * Generic setter
	 *
	 * @access public
	 * @param string $var class variable to set
	 * @param mixed $value value of class variable you want to set
	 * @since 1.0
	 */
	public function __set($var, $value)
	{
		try
		{
			$this->{__.$var} = $value;
			$this->_update_array[$var] = $value;
		}			
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception("Error: Class variable `".$var."` doesn't exists in this child model in set context"),true);		
		}
	}
	
	/**
	 * Magic method to print current object
	 * 
	 * @access public
	 * @return string $this->getParams formated current object property
	 * @since 1.0.9
	 */
	public function __toString()
	{
		return SLS_String::printArray($this->getParams(true));
	}
	
	/**
	 * Magic method to catch undefined method calling
	 * 
	 * @access public
	 * @param string $name method's name
	 * @param mixed args method's arguments
	 * @since 1.0.9
	 */
	public function __call($name, $args)
    {
        SLS_Tracing::addTrace(new Exception("Error: Try to call an undefined method `".$name."` in ".get_class($this)." model."),true);
    }
    
    /**
	 * Magic method to catch undefined static method calling
	 * 
	 * @access public
	 * @param string $name method's name
	 * @param mixed args method's arguments
	 * @since 1.0.9
	 */
	public static function __callStatic($name, $args)
    {
    	SLS_Tracing::addTrace(new Exception("Error: Try to call an undefined static method `".$name."` in ".get_class($this)." model."),true);
    }
	
	/**
	 * Reset current model
	 * 
	 * @access public
	 * @since 1.0.9
	 */
	public function clear()
	{
		$this->{__.$this->getPrimaryKey()} = null;
		foreach($this->getParams() as $key => $value)
			$this->{__.$key} = null;
		$this->_update_array = array();
		$this->buildDefaultValues();
	}
	
	/**
	 * Getter returning all class variables representing database entity	 
	 *
	 * @access public
	 * @param mixed $fks (bool: if true all fks, if false only current Model) - array fks you want to extract params
	 * @return array $params associative array with all class variables
	 * @since 1.0
	 * @example 
	 * // if the current model is map on a table named "user"
	 * var_dump($user->getParams());
	 * // will produce :
	 * array(
	 * 		"user_id"	=> 1,
	 * 		"user_email"=> "laurent@sillysmart.org",
	 * 		"user_pwd"	=> "password"
	 * )
	 * @example
	 * // if the current model is map on a table name "news" linked with a foreign key on "user" and "category"
	 * var_dump($news->getParams(true));
	 * // will produce : 
	 * array(
	 * 		"news_id" => 1,
	 * 		"news_title" => "title",
	 * 		"news_content" => "my news..."
	 * 		"user_id" => 1,
	 * 		"user_name" => "Bientz",
	 * 		"user_firstname" => "Laurent",
	 * 		"category_id" => "1",
	 * 		"category_name" => "Digital"
	 * )
	 * @example
	 * // if the current model is map on a table name "news" linked with a foreign key on "user" and "category"
	 * var_dump($news->getParams(array('user')));
	 * // will produce : 
	 * array(
	 * 		"news_id" => 1,
	 * 		"news_title" => "title",
	 * 		"news_content" => "my news..."
	 * 		"user_id" => 1,
	 * 		"user_name" => "Bientz",
	 * 		"user_firstname" => "Laurent"
	 * )
	 */
	public function getParams($fks=false,$subkey=false)
	{
		// Temp unset of non SQL class variables
		$genericTmp = $this->_generic;
		$sqlTmp = $this->_sql;
		$updateArray = $this->_update_array;		
		$this->_generic = null;
		$this->_sql = null;
		$this->_update_array = null;
		
		eval ('$params = '.  var_export($this, true) . ';');
		
		// Reset of non SQL class variables
		$this->_generic = $genericTmp;
		$this->_sql = $sqlTmp;
		$this->_update_array = $updateArray;

		// If fks params wanted
		if ($fks !== false)
		{
			// All fks linked to this model
			if ($fks === true)
				$fks = $this->_fks;
			
			// Foreach fk, merge params
			if (is_array($fks) && !empty($fks))
			{
				foreach($fks as $model)
				{
					if ($model != $this->_table)
					{
						$functionName = SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(SLS_String::tableToClass($model)," ",false)),"");
						$functionName{0} = strtolower($functionName{0});					
						
						if ($subkey)
							$params[$model] = $this->$functionName()->getParams();
						else
							$params = array_merge($params,$this->$functionName()->getParams());
					}
				}
			}
		}

		return $params;
	}
	
	/**
	 * Return all table columns
	 * 
	 * @access public
	 * @return array $params array with all table columns
	 * @since 1.0.8
	 * @example 
	 * // if the current model is map on a table named "user"
	 * var_dump($this->_generic->getColumns());
	 * // will produce :
	 * array(
	 * 		"user_id", 
	 * 		"user_email",
	 * 		"user_pwd"
	 * )
	 */
	public function getColumns()
	{
		return array_keys($this->getParams());
	}
	
	/**
	 * Return only columns name updated
	 * 
	 * @access public
	 * @return array $params array with all table columns updated
	 * @since 1.0.9
	 * @example
	 * // if the current model is map on a table name "user" 
	 * $user->setUserName("Bientz");
	 * $user->setUserBirthday("1986-04-30");
	 * $user->save(); 
	 * var_dump($user->getParamsUpdated());
	 * // will produce :
	 * array("user_name", "user_birthday");
	 */
	public function getParamsUpdated()
	{
		return array_keys($this->_update_array);
	}
	
	/**
	 * Static function used by getParams	 
	 *
	 * @access public static
	 * @param array $an_array
	 * @return array
	 * @since 1.0
	 */
	public static function __set_state($an_array)
  	{
		$array = array();
		foreach($an_array as $key=>$value)
			(substr($key, 0, 2) === "__") ? $array[substr($key, 2)] = $value : false;		
		return $array;
	}
	
  	/**
   	 * Check that class variables describing sql table and its primary key have been filled	 
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function beSurInitDbInfos() 
	{
	  if (null === $this->_table)	  
	  	SLS_Tracing::addTrace(new Exception("Error: Class variable describing current table haven't been filled"),true);
	  if (null === $this->_primaryKey)	  
	  	SLS_Tracing::addTrace(new Exception("Error: Class variable describing the primary key of the current table hasn't been filled"),true);	  
	}
  
	/**
	 * Update the current child model	 
	 *
	 * @access public
	 * @return bool true if updated, else false
	 * @see SLS_FrontModel::create
	 * @see SLS_FrontModel::delete
	 * @since 1.0
	 */
	public function save() 
	{
		if ($this->_isMultilanguage && empty($this->_modelLanguage))			
			$this->setModelLanguage();
		if (count($this->_update_array) == 0)
			return true;
		if (!is_numeric($this->{__.$this->_primaryKey}))
			return false;		
		$this->_sql->save($this->{__.$this->_primaryKey}, $this->_update_array);	
		return true;		
	}
	
	/**
	 * Search of n objects of models
	 *
	 * @access public
	 * @param string $table the current table to list (default: empty => current model)
	 * @param array $joins the table(s) to join with current table (default: empty => no join)
	 * If you want to natural join: 
	 * <code>array("table_2","table_3","...","table_n")</code> will give 'SELECT * FROM table_1 NATURAL JOIN table_2 NATURAL JOIN table_3 ... NATURAL JOIN table_n'
	 * If you want to join with a specific column: 
	 * <code>array(0=>array("table"=>"table_2","column"=>"column_2"),1=>array("table"=>"table_3","column"=>"column_3"))</code>
	 * If you want to inner/left/right join:
	 * <code>array(0=>array("table"=>"table_2","column"=>"column_2","mode"=>"inner"),1=>array("table"=>"table_3","column"=>"column_3","mode"=>"left"))</code>
	 * @param array $clause the clause wanted (default: empty => no clause)
	 * <code>
	 * array
	 * (
	 *		[0] => array
	 *				(
	 *					["column"] = "column_1",
	 *					["value"] = "value_1", // or array('value1','value2','...','valueN') if "in" mode
	 *					["mode"] = "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull" or "in" or "notin"
	 *				)
	 *		[1] => array
	 *				(
	 *					["column"] = "user_department",
	 *					["value"] = "75", // or array('value1','value2','...','valueN') if "in" mode
	 *					["mode"] = "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull" or "in" or "notin"
	 *				)
	 * )
	 * </code>
	 * @param array $group a group by (default: empty => no group by)
	 * <code>array("column_1","column_2","...","column_n")</code>
	 * @param mixed $order the order you want (default: empty => ORDER BY primary key ASC)
	 * <code>
	 * array
	 * (
	 * 		[0] => array
	 * 				(
	 * 					["column"] = "column_1",
	 * 					["order"] = "asc"
	 * 				)
	 * 		[1] => array
	 * 				(
	 * 					["column"] = "column_2",
	 * 					["order"] = "desc"
	 * 				)
	 * )
	 * </code>
	 * or
	 * <code>
	 * array
	 * (
	 * 		"column_1" => "asc",
	 * 		"column_2" => "desc"
	 * )
	 * </code>
	 * or
	 * <code>string "rand()"</code>
	 * @param array $limit the limit you want (default: empty for all recordsets)
	 * <code>array("start" => "10", "length" => "30")</code>
	 * or
	 * <code>array("10" => "30")</code>
	 * @return array $objects array of PDO objects
	 * @see SLS_FrontModel::countModels
	 * @since 1.0
	 * @example 
	 * // Find all recordsets
	 * $object->searchModels();
	 * @example 
	 * // Find all recordsets where column_name like 'is'
	 * $object->searchModels("table_name",array(),array(0=>array("column" => "colu_name", "value" => "is", "mode" => "like")));
	 */
	public function searchModels($table="",$joins=array(),$clause=array(),$group=array(),$order=array(),$limit=array())
	{
		if (!empty($order) && is_array($order) && !is_array($order[0]))
		{
			$realOrder = array();
			foreach($order as $column => $way)
				$realOrder[] = array("column" => $column, "order" => $way);
			$order = $realOrder;
		}
		
		return $this->_sql->searchModels($table,$joins,$clause,$group,$order,$limit);
	}
	
	/**
	 * Count the number of objects of models
	 *	 
	 * @access public
	 * @param string $table the current table to list (default: empty => current model)
	 * @param array $joins the table(s) to join with current table (default: empty => no join)
	 * If you want to natural join: 
	 * <code>array("table_2","table_3","...","table_n")</code> will give 'SELECT * FROM table_1 NATURAL JOIN table_2 NATURAL JOIN table_3 ... NATURAL JOIN table_n'
	 * If you want to join with a specific column: 
	 * <code>array(0=>array("table"=>"table_2","column"=>"column_2"),1=>array("table"=>"table_3","column"=>"column_3"))</code>
	 * If you want to inner/left/right join:
	 * <code>array(0=>array("table"=>"table_2","column"=>"column_2","mode"=>"inner"),1=>array("table"=>"table_3","column"=>"column_3","mode"=>"left"))</code>
	 * @param array $clause the clause wanted (default: empty => no clause)
	 * <code>
	 * array
	 * (
	 *		[0] => array
	 *				(
	 *					["column"] = "column_1",
	 *					["value"] = "value_1", // or array('value1','value2','...','valueN') if "in" mode
	 *					["mode"] = "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull" or "in"
	 *				)
	 *		[1] => array
	 *				(
	 *					["column"] = "user_department",
	 *					["value"] = "75", // or array('value1','value2','...','valueN') if "in" mode
	 *					["mode"] = "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull" or "in"
	 *				)
	 * )
	 * </code>
	 * @param array $group a group by (default: empty => no group by)
	 * <code>array("column_1","column_2","...","column_n")</code>
	 * @return int $nbResults the number of objects within limit
	 * @see SLS_FrontModel::searchModels
	 * @since 1.0
	 */
	public function countModels($table,$joins=array(),$clause=array(),$group=array())
	{
		return $this->_sql->countModels($table,$joins,$clause,$group);
	}
	
	/**
	 * Delete n objects of models
	 * 
	 * @param string $table the current table to delete (default: empty => current model)
	 * @param array $joins the table(s) to join with current table (default: empty => no join)
	 * If you want to natural join: 
	 * <code>array("table_2","table_3","...","table_n")</code> will give 'DELETE FROM table_1 NATURAL JOIN table_2 NATURAL JOIN table_3 ... NATURAL JOIN table_n'
	 * If you want to join with a specific column: 
	 * <code>array(0=>array("table"=>"table_2","column"=>"column_2"),1=>array("table"=>"table_3","column"=>"column_3"))</code>
	 * If you want to inner/left/right join:
	 * <code>array(0=>array("table"=>"table_2","column"=>"column_2","mode"=>"natural"),1=>array("table"=>"table_3","column"=>"column_3","mode"=>"left"))</code>
	 * @param array $clause the clause wanted (default: empty => no clause)
	 * <code>
	 * array
	 * (
	 *		[0] => array
	 *				(
	 *					["column"] = "column_1",
	 *					["value"] = "value_1",
	 *					["mode"] = "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull"
	 *				)
	 *		[1] => array
	 *				(
	 *					["column"] = "user_department",
	 *					["value"] = "75",
	 *					["mode"] = "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull"
	 *				)
	 * )
	 * </code>
	 * @return int $count the number of lines deleted
	 * @see SLS_FrontModel::searchModels
	 * @since 1.0.6
	 */
	public function deleteModels($table,$join,$clause)
	{
		return $this->_sql->deleteModels($table,$join,$clause);
	}
	
	/**
	 * Give the next id from the current model
	 *
	 * @access public
	 * @return int $nextId the next id
	 * @since 1.0
	 */
	public function giveNextId()
	{
		$result = $this->_sql->giveNextId();
		
		return (empty($result)) ? 1 : ($result->nextId+1);
	}
	
	/**
	 * Get the comment of a table
	 *
	 * @access public	 
	 * @param string $table the table name (current child model if empty)
	 * @param string $db the db alias (if empty, current db)
	 * @return string $comment the comment on the table
	 * @see SLS_FrontModel::setTableComment
	 * @see SLS_FrontModel::getColumnComment
	 * @see SLS_FrontModel::setColumnComment
	 * @since 1.0
	 */
	public function getTableComment($table="",$db="")
	{
		$table = (empty($table)) ? $this->_table : $table;
		
		return $this->_sql->getTableComment($table,$db);
	}
	
	/**
	 * Set the comment of a table
	 *
	 * @access public	 
	 * @param string $comment the comment to save
	 * @param string $table the table name (current child model if empty)
	 * @param string $db the db alias (if empty, current db)
	 * @return bool true if saved, else false
	 * @see SLS_FrontModel::getTableComment
	 * @see SLS_FrontModel::getColumnComment
	 * @see SLS_FrontModel::setColumnComment
	 * @since 1.0
	 */
	public function setTableComment($comment,$table="",$db="")
	{
		$table = (empty($table)) ? $this->_table : $table;
		
		return $this->_sql->setTableComment($comment,$table,$db);
	}
	
	/**
	 * Get the comment of a column
	 *
	 * @access public
	 * @param string $column the column name
	 * @param string $table the table name (current child model if empty)
	 * @param string $db the db alias (if empty, current db)
	 * @return string $comment the comment on a column
	 * @see SLS_FrontModel::getTableComment
	 * @see SLS_FrontModel::setTableComment
	 * @see SLS_FrontModel::setColumnComment
	 * @since 1.0
	 */
	public function getColumnComment($column,$table="",$db="")
	{
		$table = (empty($table)) ? $this->_table : $table;
		
		return $this->_sql->getColumnComment($column,$table,$db);
	}
	
	/**
	 * Set the comment of a column
	 *
	 * @access public
	 * @param string $column the column name
	 * @param string $comment the comment to save
	 * @param string $table the table name (current child model if empty)
	 * @param string $db the db alias (if empty, current db)
	 * @return bool true if saved, else false
	 * @see SLS_FrontModel::getTableComment
	 * @see SLS_FrontModel::setTableComment
	 * @see SLS_FrontModel::getColumnComment
	 * @since 1.0
	 */
	public function setColumnComment($column,$comment,$table="",$db="")
	{
		$table = (empty($table)) ? $this->_table : $table;
		
		return $this->_sql->setColumnComment($column,$comment,$table,$db);
	}
	
	/**
	 * Get the default value for a column of a table
	 * 
	 * @access public
	 * @param string $column the column name
	 * @param string $table the table name (current child model if empty)
	 * @param string $db the db alias (if empty, current db)
	 * @since 1.0.9
	 */
	public function getColumnDefault($column,$table="",$db="")
	{
		$table = (empty($table)) ? $this->_table : $table;
		
		return $this->_sql->getColumnDefault($column,$table,$db);
	}
	
	/**
	 * Check if a recordset described by a column doesn't already exists
	 *
	 * @access public
	 * @param string $column the column name
	 * @param string $value the column value
	 * @param string $table the table to check (current table model if empty)
	 * @param string $excludedColumn the column name to exclude
	 * @param string $excludedValue the column value to exclude
	 * @return bool $isUnique true if no recordset has been found, else false
	 * @since 1.0
	 */
	public function isUnique($column,$value,$table="",$excludedColumn="",$excludedValue="")
	{
		$table = (empty($table)) ? $this->_table : $table;
		$columns = array();
		
		if ($this->_generic->useModel(SLS_String::tableToClass($table),$this->_db,"user") || $this->_generic->useModel(SLS_String::tableToClass($table),$this->_db,"sls"))
		{
			$className = ucfirst(strtolower($this->_db))."_".SLS_String::tableToClass($table);
			$object = new $className();
			foreach($object->getParams() as $key => $cur_value)
				array_push($columns,$key);
		}
		
		if (!in_array($column,$columns))
		{
			SLS_Tracing::addTrace(new Exception("Error: Column ".$column." doesn't exist in table `".$table."`"),true);
			return false;
		}
		if (!empty($excludedColumn) && !empty($excludedValue))
		{
			if (!in_array($excludedColumn,$columns))
			{
				$excludedColumn = "";
				$excludedValue = "";
				SLS_Tracing::addTrace(new Exception("Warning: Column ".$excludedColumn." to exclude for the unique recordset test doesn't exist in table `".$table."` - exclude ommited"),true);
			}
		}
			
		return ($this->_sql->isUnique($column,$value,$table,$excludedColumn,$excludedValue)==0) ? true : false;
	}
	
	/**
	 * Perform a call on a specific function of the sql part on the current model
	 * First parameter should be the name of the function ; after you can pass as many parameters as you need
	 * 
	 * @return mixed bool if an error occured, else the return of your sql function
	 * @since 1.0.6
	 */
	public function callSqlFunction()
	{
		$args = func_get_args();
		if (empty($args) || !is_array($args))
		{
			SLS_Tracing::addTrace(new Exception("Error: You try to call a specific SQL function without specify it's name in table `".$this->getTable()."`"),true);
			return false;
		}
		else
		{
			$functionName = array_shift($args);
			if (!method_exists($this->_sql,$functionName))
			{
				SLS_Tracing::addTrace(new Exception("Error: Specific SQL function `".$functionName."` doesn't exist in table `".$this->getTable()."`"),true);
				return false;
			}
			else
			{				
				$ref = new ReflectionMethod($this->_sql,$functionName);
				$nbRequiredParams = $ref->getNumberOfRequiredParameters();
				$nbMaxParams = $ref->getNumberOfParameters();
				if ($nbRequiredParams > count($args))
				{
					SLS_Tracing::addTrace(new Exception("Error: Specific SQL function `".$functionName."` needs ".$nbRequiredParams." required parameters in table`".$this->getTable()."`"),true);
					return false;
				}
				if ($nbMaxParams < count($args))
					SLS_Tracing::addTrace(new Exception("Warning: Specific SQL function `".$functionName."` has only ".$nbMaxParams." parameters in table`".$this->getTable()."`, you call it with ".count($args)." parameters"),true);
				
				return $ref->invokeArgs($this->_sql,$args);
			}			
		}		
	}
	
	/**
	 * Get foreign key recordsets of a given table 
	 * 
	 * @param string $tableSource
	 * @param array $tableFk
	 * @param string $pkFk
	 * @param array $columnsToSelect	 
	 * @param string $clause
	 * @param string $lang
	 * @param string $value
	 * @param int $nb
	 * @param int $id
	 * @since 1.0.6
	 */
	public function fkAC($tableSource,$tableFk,$pkFk,$columnsToSelect,$clause="",$lang="",$value="",$nb=10,$id=0)
	{		
		return $this->_sql->fkAC($tableSource,$tableFk,$pkFk,$columnsToSelect,$clause,$lang,$value,$nb,$id);
	}
	
	/**
	 * Sql Exec
	 *
	 * @access public
	 * @param string $exec the sql query	 
	 * @since 1.0.6
	 */
	public function exec($exec) 
	{
		return $this->_sql->exec($exec);
	}
	
	/**
	 * Begin Transaction
	 * 
	 * @access public
	 * @since 1.0.6
	 */
	public function beginTransaction()
	{
		return $this->_sql->beginTransaction();
	}
	
	/**
	 * Commit Transaction
	 * 
	 * @access public
	 * @since 1.0.6
	 */
	public function commitTransaction()
	{
		return $this->_sql->commitTransaction();
	}
	
	/**
	 * Rollback Transaction
	 * 
	 * @access public
	 * @since 1.0.6
	 */
	public function rollbackTransaction()
	{
		return $this->_sql->rollbackTransaction();
	}
	
	/**
	 * Format XML for a given recordsets collection
	 * 
	 * @access public
	 * @param SLS_XMLToolbox $xml current controller's XML
	 * @param array $recordsets array of PDO recordsets
	 * @param array $options transformations on some columns - delimited by ":". each function can be methods of SLS' classes or php standard function
	 * <code>
     * // Complete example
	 * $newss = $news->searchModels("news",array("user","article_category"));
	 * $xml = $news->pdoToXML($xml,$newss,array("news_excerpt" => array("php:strip_tags","SLS_String:trimStringToLength:100"),
	 *											"news_date" => array("SLS_Date:getDate:FULL_LITTERAL_TIME","php:ucwords"),
	 *											"news_photo" => "SLS_String:getUrlFileImg:_0",
	 *											"news_pdf" => "SLS_String:getUrlFile",
	 *											"news_title" => "php:trim")
	 *						, "all_news/news");
	 * </code>
	 * @param string $nodeName the root node of your model, by default it's your classname in lowercase
	 * @return SLS_XMLToolbox $xml current controller's XML updated
	 * @see SLS_FrontModel::getParams
	 * @see SLS_FrontModel::toXML
	 * @since 1.0.8	 
	 */
	public function pdoToXML($xml,$recordsets,$options=array(),$nodeNames="")
	{
		$nodeName 	= (SLS_String::contains($nodeNames,"/")) ? SLS_String::substrAfterLastDelimiter($nodeNames,"/") : "";
		$nodeNames 	= (SLS_String::contains($nodeNames,"/")) ? SLS_String::substrBeforeFirstDelimiter($nodeNames,"/") : strtolower($this->getTable())."s";
		
		$xml->startTag($nodeNames);
		for($i=0 ; $i<$count=count($recordsets) ; $i++)		
			$xml = $this->toXML($xml,$options,array(),$nodeName,SLS_String::objectToArray($recordsets[$i]));		
		$xml->endTag($nodeNames);
		
		return $xml;
	}
	
	/**
	 * Format XML for the current recordset
	 * 
	 * @access public
	 * @param SLS_XMLToolbox $xml current controller's XML
	 * @param array $options transformations on some columns - delimited by ":". each function can be methods of SLS' classes or php standard function
	 * <code>
     * // Complete example
	 * $xml = $news->toXML($xml, array( "news_excerpt" 	=> array("php:strip_tags", "SLS_String:trimStringToLength:100"),
	 *									"news_date" 	=> array("SLS_Date:getDate:FULL_LITTERAL_TIME", "php:ucwords"),
	 *									"news_photo" 	=> "SLS_String:getUrlFileImg:_0",
	 *									"news_pdf" 		=> "SLS_String:getUrlFile",
	 *									"news_title" 	=> "php:trim",
	 *									"news_link"		=> array("/Item/",
	 *															 "news_title" => "SLS_String:stringToUrl:_",
	 *															 "-",
	 *															 "news_id" => array("php:intval","php:pow:2"),
	 *															 "/User/",
	 *															 "user_id"))
	 *						, true, "news");
	 * </code>    
	 * @param mixed $fks (bool: if true all fks, if false only current Model) - array fks you want to extract params
	 * @param string $nodeName the root node of your model, by default it's your classname in lowercase
	 * @param array $properties all columns/values of your choice if you don't want to take it from getParams() function of the current instance
	 * @return SLS_XMLToolbox $xml current controller's XML updated
	 * @see SLS_FrontModel::getParams
	 * @see SLS_FrontModel::pdoToXML
	 * @since 1.0.8	 
	 */
	public function toXML($xml,$options=array(),$fks=false,$nodeName="",$properties=array())
	{
		$nodeName 	= (empty($nodeName)) ? strtolower($this->getTable()) : $nodeName;
		$properties = (empty($properties)) ? $this->getParams($fks) : $properties;
		
		$xml->startTag($nodeName);
		foreach($properties as $column => $value)
		{
			if (in_array($column,$columns=array_keys($options)))
			{
				$filters = (is_array($options[$column])) ? $options[$column] : array($options[$column]);
				
				foreach($filters as $filter)
				{
					$option = explode(":",$filter);
					switch($option[0])
					{
						case SLS_String::startsWith($option[0],"SLS_"):
							if (!class_exists($option[0]))
								SLS_Tracing::addTrace(new Exception("Error: you want to use an undefined class `".$option[0]."` for the column `".$column."` of `".$this->getTable()."` table"));
							else
							{
								if (!method_exists($option[0],((count($option)> 1) ? $option[1] : "")))
									SLS_Tracing::addTrace(new Exception("Error: you want to use an undefined function `".$option[1]."` of class `".$option[0]."` for the column `".$column."` of `".$this->getTable()."` table"));
								else
								{
									$ref = new ReflectionMethod($option[0],$option[1]);
									$nbRequiredParams = $ref->getNumberOfRequiredParameters();
									if ($nbRequiredParams > (count($option)-1))									
										SLS_Tracing::addTrace(new Exception("Error: function `".$option[1]."` of class `".$option[0]."` needs ".$nbRequiredParams." required parameters for the column `".$column."` of `".$this->getTable()."` table"),true);
									else
									{										
										$params = array_slice($option,2);
										array_unshift($params,$value);
										
										// Case "SLS_Date::getDate"
										if ($option[0] == "SLS_Date" && $option[1] == "getDate")
										{											
											$option[0] = new SLS_Date($value);
											array_shift($params);											
										}
										// Case "SLS_String::getUrlFileImg"
										if ($option[0] == "SLS_String" && $option[1] == "getUrlFileImg" && count($option) > 2)
										{
											$xml->addFullTag($column."_original",SLS_String::getUrlFile($value,(count($option) > 3) ? $option[3] : ""),true);
											$column = $column.$option[2];											
										}
										
										$value = $ref->invokeArgs(($ref->isStatic()) ? null : $option[0],$params);
										
									}									
								}	
							}	
							break;				
						case "php":
							if (count($option) < 2)
								SLS_Tracing::addTrace(new Exception("Error: you must specify the name of the PHP's function you want to apply on the column `".$column."` of `".$this->getTable()."` table"));
							else
							{
								if (function_exists($option[1]))
								{
									$ref = new ReflectionFunction($option[1]);
									$nbRequiredParams = $ref->getNumberOfRequiredParameters();
									if ($nbRequiredParams > (count($option)-1))									
										SLS_Tracing::addTrace(new Exception("Error: the PHP's function `".$option[1]."` needs ".$nbRequiredParams." required parameters for the column `".$column."` of `".$this->getTable()."` table"),true);
									else
									{
										$params = array_slice($option,2);
										array_unshift($params,$value);
										$value = $ref->invokeArgs($params);
									}
								}
								else
									SLS_Tracing::addTrace(new Exception("Error: the PHP's function '".$option[1]."' you want to use on the column `".$column."` of `".$this->getTable()."` table doesn't exist"));
							}
							break;
						default:
							SLS_Tracing::addTrace(new Exception("Error: you want to apply an unknown filter on the column `".$column."` of `".$this->getTable()."` table doesn't exist"));
							break;
					}
				}
			}
			$xml->addFullTag($column,$value,true);
		}		
		foreach($options as $col => $concat)
		{
			if (!in_array($col,array_keys($properties)) && is_array($concat))
			{
				$values = array();				
				foreach($concat as $column => $filter)
				{
					if (is_int($column) && !empty($filter) && !is_array($filter))
					{
						$column = $filter;
						$filter = "";
					}
					$value = "";
					$filters = (is_array($filter)) ? $filter : ((empty($filter)) ? "" : array($filter));					
					if (in_array($column,array_keys($properties)))
						$value .= $properties[$column];
					else
						$value .= $column;
					
					if (!empty($filters))
					{
						foreach($filters as $filter)
						{
							$option = explode(":",$filter);
							
							switch($option[0])
							{
								case SLS_String::startsWith($option[0],"SLS_"):
									if (!class_exists($option[0]))
										SLS_Tracing::addTrace(new Exception("Error: you want to use an undefined class `".$option[0]."` for the column `".$column."` of `".$this->getTable()."` table"));
									else
									{
										if (!method_exists($option[0],((count($option)> 1) ? $option[1] : "")))
											SLS_Tracing::addTrace(new Exception("Error: you want to use an undefined function `".$option[1]."` of class `".$option[0]."` for the column `".$column."` of `".$this->getTable()."` table"));
										else
										{
											$ref = new ReflectionMethod($option[0],$option[1]);
											$nbRequiredParams = $ref->getNumberOfRequiredParameters();
											if ($nbRequiredParams > (count($option)-1))									
												SLS_Tracing::addTrace(new Exception("Error: function `".$option[1]."` of class `".$option[0]."` needs ".$nbRequiredParams." required parameters for the column `".$column."` of `".$this->getTable()."` table"),true);
											else
											{										
												$params = array_slice($option,2);
												array_unshift($params,$value);
												
												// Case "SLS_Date::getDate"
												if ($option[0] == "SLS_Date" && $option[1] == "getDate")
												{											
													$option[0] = new SLS_Date($value);
													array_shift($params);											
												}
												// Case "SLS_String::getUrlFileImg"
												if ($option[0] == "SLS_String" && $option[1] == "getUrlFileImg" && count($option) > 2)
												{
													$xml->addFullTag($column."_original",SLS_String::getUrlFile($value),true);
													$column = $column.$option[2];											
												}
												
												$value = $ref->invokeArgs(($ref->isStatic()) ? null : $option[0],$params);
												
											}									
										}	
									}	
									break;				
								case "php":
									if (count($option) < 2)
										SLS_Tracing::addTrace(new Exception("Error: you must specify the name of the PHP's function you want to apply on the column `".$column."` of `".$this->getTable()."` table"));
									else
									{
										if (function_exists($option[1]))
										{
											$ref = new ReflectionFunction($option[1]);
											$nbRequiredParams = $ref->getNumberOfRequiredParameters();
											if ($nbRequiredParams > (count($option)-1))									
												SLS_Tracing::addTrace(new Exception("Error: the PHP's function `".$option[1]."` needs ".$nbRequiredParams." required parameters for the column `".$column."` of `".$this->getTable()."` table"),true);
											else
											{
												$params = array_slice($option,2);
												array_unshift($params,$value);
												$value = $ref->invokeArgs($params);
											}
										}
										else
											SLS_Tracing::addTrace(new Exception("Error: the PHP's function '".$option[1]."' you want to use on the column `".$column."` of `".$this->getTable()."` table doesn't exist"));
									}
									break;
								default:
									SLS_Tracing::addTrace(new Exception("Error: you want to apply an unknown filter on the column `".$column."` of `".$this->getTable()."` table doesn't exist"));
									break;
							}
						}
					}
					$values[] = $value;
				}
				$xml->addFullTag($col,implode("",$values),true);
			}
		}
		$xml->endTag($nodeName);
		
		return $xml;
	}
}
?>