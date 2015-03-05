<?php
/**
 * Mother class of SQL models
 * 
 * @author Florian Collot
 * @author Laurent Bientz
 * @copyright SillySmart
 * @package Sls.Models.Core  
 * @see SLS_Frontmodel
 * @since 1.0
 */
class SLS_FrontModelSql
{
	// Class variables	
	protected $_db;
	protected $_table;
	protected $_primaryKey;
	protected $_isMultilanguage = false;
	protected $_modelLanguage = "";	
	protected $_generic;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $table SQL table describing current child model
	 * @param string $primaryKey SQL primary key describing current child model
	 * @param bool $multilanguage true if the we have multilanguage content, else false
	 * @since 1.0
	 */
	public function __construct($table,$primaryKey,$multilanguage=false) 
	{
		$this->_isMultilanguage = ($multilanguage) ? true : false;
		
		$this->_db = SLS_Sql::getInstance();
		$this->_generic = SLS_Generic::getInstance();
		$this->_table = $table;
		$this->_primaryKey = $primaryKey;
	}
	
	/**
	 * Set the current language of the model
	 *
	 * @access public
	 * @param string $lang the current language of the model (generic lang if not filled)
	 * @since 1.0
	 */
	public function setModelLanguage($lang="")
	{
		if ($this->_isMultilanguage)
		{
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
		$sql = "SELECT "."\n".
				"    * "."\n".
				"FROM "."\n".
				"    `".$this->_table."` "."\n".
				"WHERE "."\n".
				"    `".$this->_primaryKey."` = ".$this->_db->quote($id)." "."\n";
		if ($this->_isMultilanguage)
			$sql .= "    AND `pk_lang` = ".$this->_db->quote($this->_modelLanguage)." "."\n";
		$sql .= "LIMIT "."\n". 
				"    1";
		return array_shift($this->_db->select($sql));
	}
	
	/**
	 * Create child model
	 *
	 * @access public
	 * @param array $params array of keys/values to insert
	 * @param int $pkMultiLang one of the PKs described the entity in the case of multilanguage content (empty by default)
	 * @see SLSFrontModelSql::delete
	 * @see SLSFrontModelSql::save
	 * @since 1.0
	 */
	public function create($params,$pkMultiLang="")
	{
		$columns = $this->_db->showColumns($this->_table);
		if ($columns == false)
			return false;
		else
		{
			$sqlColumn = "";
			$sqlValues = "";
			for($i=0 ; $i<$count=count($columns) ; $i++)
			{				
				// Primary key(s) cases
				if (strtolower($columns[$i]->Key) == "pri")
				{
					if ($columns[$i]->Field != "pk_lang" && $pkMultiLang != NULL)
					{
						$sqlColumn .= "    `".$columns[$i]->Field."`, "."\n";
						$sqlValues .= "    ".$this->_db->quote($pkMultiLang).", "."\n";
					}
					else if ($columns[$i]->Field == "pk_lang" && $this->_isMultilanguage)
					{
						$sqlColumn .= "    `".$columns[$i]->Field."`, "."\n";
						$sqlValues .= "    ".$this->_db->quote($this->_modelLanguage).", "."\n";
					}					
				}
				// Others cases
				if (strtolower($columns[$i]->Key) != "pri")
				{
					if (($params[$columns[$i]->Field] != NULL))
					{
						$sqlColumn .= "    `".$columns[$i]->Field."`, "."\n";
						$sqlValues .= "    ".$this->_db->quote($params[$columns[$i]->Field]).", "."\n";
					}
				}
			}
			$sqlColumn = SLS_String::substrBeforeLastDelimiter($sqlColumn,",");
			$sqlValues = SLS_String::substrBeforeLastDelimiter($sqlValues,",");
			$sql = "INSERT INTO "."\n".
					"    `".$this->_table."` ("."\n".
					$sqlColumn."\n".
					") ".
					"    VALUES ("."\n".
					$sqlValues."\n".
					")";			
			try
			{
				$lastId = $this->_db->insert($sql);
				if ($this->_isMultilanguage && $pkMultiLang != NULL)
					$lastId = $pkMultiLang;
					
				return $lastId;
			}
			catch (Exception $e)
			{
				SLS_Tracing::addTrace($e,true);
				return false;
			}
		}
	}
	
	/**
	 * Get child model entity described by primary key
	 *
	 * @access public
	 * @param int $id primary key of wanted model
	 * @return array PDO Object
	 * @since 1.0
	 */
	public function get($id)
	{		
		if (!is_numeric($id))
			return false;
		try
		{				
			$sql = "SELECT "."\n".
				   "    *"."\n". 
				   "FROM "."\n".
				   "    `".$this->_table."`"."\n".
				   "WHERE "."\n".
				   "    ".$this->_primaryKey." = ".$this->_db->quote($id)." \n";			
			if ($this->_isMultilanguage)
				$sql .= "    AND `pk_lang` = ".$this->_db->quote($this->_modelLanguage)." "."\n";			
			$sql .= "LIMIT ".
					"    1";			
			return $this->_db->select($sql);
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
		
	/**
	 * Update the current child model
	 * 	 
	 * @access public
	 * @param int $id primary key of the current model
	 * @param array $array associative array with modified keys/values
	 * @return bool true if updated, else false
	 * @see SLSFrontModelSql::create
	 * @see SLSFrontModelSql::delete
	 * @since 1.0
	 */
	public function save($id, $array) 
	{
		if (!is_numeric($id))
			return false;
		if ( $this->get($id) === false)
			return false;
		if (!is_array($array) || count($array) == 0)
			return false;

		$columns = array();
		$insertSql = '';
		
		if ($this->_generic->useModel(SLS_String::tableToClass($this->_table),$this->_db->getCurrentDb(),"user") || $this->_generic->useModel(SLS_String::tableToClass($this->_table),$this->_db->getCurrentDb(),"sls"))
		{
			$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($this->_table);
			$object = new $className();
			foreach($object->getParams() as $key => $value)
				array_push($columns,$key);
		}
		else
			return false;
		
		foreach ($array as $key => $value) 
		{
			if (in_array($key,$columns))
				$insertSql .= "    `".$key."` = ".$this->_db->quote($value).", "."\n";
			else
				SLS_Tracing::addTrace(new Exception("Warning: Column ".$key." doesn't exist in table `".$this->_table."`"),true);
		}
		if (!empty($insertSql)) 
		{	
			$insertSql = SLS_String::substrBeforeLastDelimiter($insertSql,",");
			$sql = "UPDATE "."\n".
				   "    `".$this->_table."` "."\n"."
				   SET "."\n".
				   $insertSql." "."\n".
				   "WHERE "."\n".
				   "    ".$this->_primaryKey." = ".$this->_db->quote($id)." "."\n";
			if ($this->_isMultilanguage)
				$sql .= "    AND `pk_lang` = ".$this->_db->quote($this->_modelLanguage)." "."\n";
			$sql .= "LIMIT "."\n".
					"    1";
			try
			{
				$this->_db->update($sql);
			}
			catch (Exception $e)
			{			
				SLS_Tracing::addTrace($e,true);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Delete child model
	 *
	 * @access public
	 * @param int $id primary key of the current model
	 * @param bool $allLangs true if you want to delete current recordset in all languages, else only the current lang
	 * @return mixed nb recordsets deleted if success, else false
	 * @see SLSFrontModelSql::create
	 * @see SLSFrontModelSql::save
	 * @since 1.0
	 */
	public function delete($id,$allLangs=false) 
	{
		if ($this->get($id) === false)
			return false;
		$sql = "DELETE FROM "."\n".
				"    `".$this->_table."` "."\n".
				"WHERE "."\n".
				"    `".$this->_primaryKey."` = ".$this->_db->quote($id)." "."\n";
		if ($this->_isMultilanguage && !$allLangs)
			$sql .= "    AND `pk_lang` = ".$this->_db->quote($this->_modelLanguage)." "."\n";
		$sql .= " LIMIT "."\n".
				"    ".(($this->_isMultilanguage && $allLangs) ? count($this->_generic->getObjectLang()->getSiteLangs()) : "1");
		try
		{	
			return $this->_db->delete($sql);
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Search of n objects of models
	 *
	 * @access public
	 * @param string $table the current table to list (default: empty => current model)
	 * @param array $joins the table(s) to join with current table (default: empty => no join)
	 * If you want to natural join: 
	 * <code>array("table_2","table_3","...","table_n") will give 'SELECT * FROM table_1 NATURAL JOIN table_2 NATURAL JOIN table_3 ... NATURAL JOIN table_n'</code>
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
	 *					["column"] 	= "column_1",
	 *					["value"] 	= "value_1", // or array('value1','value2','...','valueN') if "in" mode
	 *					["mode"]	= "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull" or "in" or "notin"
	 *				)
	 *		[1] => array
	 *				(
	 *					["column"] 	= "user_department",
	 *					["value"] 	= "75", // or array('value1','value2','...','valueN') if "in" mode
	 *					["mode"]	= "like" or "notlike" or "beginwith" or "endwith" or "equal" or "notequal" or "lt" or "le" or "ge" or "gt" or "null" or "notnull" or "in" or "notin"
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
	 * 					["column"] 	= "column_1",
	 * 					["order"]	= "asc"
	 * 				)
	 * 		[1] => array
	 * 				(
	 * 					["column"] 	= "column_2",
	 * 					["order"]	= "desc"
	 * 				)
	 * )
	 * </code>
	 * or
	 * <code>
	 * array
	 * (
	 * 		"column_1" = "asc",
	 * 		"column_2" = "desc"
	 * )
	 * </code>
	 * or
	 * <code>string "rand()"</code>
	 * @param array $limit the limit you want (default: empty for all recordsets)
	 * <code>array("start" => "10", "length" => "30")</code>
	 * or
	 * <code>array("10" => "30")</code>	
	 * or
	 * <code>array("10")</code> 
	 * @return array PDO Object
	 * @since 1.0
	 */
	public function searchModels($table="",$joins=array(),$clause=array(),$group=array(),$order=array(),$limit=array())
	{
		$allowToJoin = false;
		$modeJoin = "inner";
		$modesJoin = array("inner","left","right");
		$columns = array();
		
		/**
		 * TABLE NAME
		 */
		// If table name haven't been filled, try to recover table name from the current model
		if (empty($table))
		{
			$table = (empty($this->_table)) ? substr(get_class($this),0,strlen(get_class($this))-3) : $this->_table;
		}
		// If table name is again empty, throw a Sls Exception
		if (empty($table))
		{
			SLS_Tracing::addTrace(new Exception("Error: Table's name has been omitted"),true);
			return false;
		}
		// If model doesn't exists
		if ($this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"user") || $this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"sls"))
		{
			$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($table);
			$object = new $className();
			foreach($object->getParams() as $key => $value)
				array_push($columns,$key);
		}
		else
		{
			SLS_Tracing::addTrace(new Exception("Error: Table `".$table."` doesn't exist in database `".($this->_db->getCurrentDb())."`"),true);
			return false;
		}
		/**
		 * /TABLE NAME
		 */
				
		
		/**
		 * JOINS
		 */
		// Get all the columns of the current table
		$columnsMain = $columns;
		$joinMain = $table;
		
		// If we want to join tables
		if (is_array($joins) && !empty($joins))
		{
			// Foreach tables to join
			foreach ($joins as $currentJoin)
			{
				// If we want joins with the clause "using"
				if (is_array($currentJoin))
				{
					// Override join mode ?
					if (array_key_exists("mode",$currentJoin))					
						$modeJoin = (in_array(strtolower($currentJoin["mode"]),$modesJoin)) ? strtolower($currentJoin["mode"]) : array_shift($modesJoin);
					
					$currentJoin = $currentJoin["table"];
				}
				
				// If the table to join doesn't exists in MySQL, throw a Sls Exception
				if (!$this->_generic->useModel(SLS_String::tableToClass($currentJoin),$this->_db->getCurrentDb(),"user") && !$this->_generic->useModel(SLS_String::tableToClass($currentJoin),$this->_db->getCurrentDb(),"sls"))
					SLS_Tracing::addTrace(new Exception("Warning: Table `".$currentJoin."` to join with `".$joinMain."` doesn't exist in database `".(SLS_Generic::getInstance()->getDbConfig("base"))."`"),true);
				 
				// Else check if we can join
				else 
				{
					$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($currentJoin);
					$objectJoin = new $className();
					$columnsJoin = array();
					
					// Get all the columns of the table to join
					foreach($objectJoin->getParams() as $key => $value)
						array_push($columnsJoin,$key);
					
					// If we want joins with the clause "using", allow to join and merge the columns of the table to join with all the columns already listed
					if (is_array($currentJoin))
					{
						$allowToJoin = true;
						$columnsMain = array_merge($columnsMain,$columnsJoin);	
					}
					// Else if we want a "NATURAL JOIN", check if we have a common key
					else
					{
						// Foreach columns of the current table
						foreach($columnsMain as $tMain)
						{
							// Foreach columns of the table to join
							foreach($columnsJoin as $tJoin)
							{
								// If we have a common column, allow to join and merge the columns of the table to join with all the columns already listed
								if ($tJoin == $tMain)
								{
									$allowToJoin = true;
									$columnsMain = array_merge($columnsMain,$columnsJoin);
								}
							}
						}
					}
					
					// If we can't join, throw a Sls Exception
					if (!$allowToJoin)
					{
						SLS_Tracing::addTrace(new Exception("Warning: Table `".$currentJoin."` to join with `".$joinMain."` doesn't have a common key"),true);
						$joins = array();
						break;
					}
					
					// Move the reference table
					$joinMain = $currentJoin;
				}
			}
		}
		
		// Build the start of the query
		$sql = "SELECT "."\n".
				"    * "."\n".
				"FROM "."\n".
				"    `".$table."` ";
		
		// If we can join, build the join
		if ($allowToJoin && is_array($joins) && !empty($joins))
		{
			foreach ($joins as $currentJoin)
			{
				if (is_array($currentJoin))
					$sql .= strtoupper($modeJoin)." JOIN "."\n".
					"    `".$currentJoin["table"]."` USING(".$currentJoin["column"].") ";
				else
					$sql .= "NATURAL JOIN "."\n".
					"    `".$currentJoin."` ";
			}
		}
		/**
		 * /JOINS
		 */
		
		
		/**
		 * CLAUSE
		 */
		// If we want a clause where
		if (!empty($clause))
		{			
			$lastSucceeded = false;
			// Foreach items to restrict
			for($i=0 ; $i<count($clause) ; $i++)
			{
				// If column on which you want to apply a restrict clause is in the list of columns collected on the tables
				$columnName = (SLS_String::contains($clause[$i]["column"],".")) ? SLS_String::substrAfterLastDelimiter($clause[$i]["column"],".") : $clause[$i]["column"];
				if (in_array($columnName,$columnsMain))
				{
					$sql .= ($lastSucceeded) ? " AND "."\n" : "\n"."WHERE "."\n";
					
					// Build the correct statement
					if (SLS_String::contains($clause[$i]["column"],"."))
						$clause[$i]["column"] = "`".SLS_String::substrBeforeLastDelimiter($clause[$i]["column"],".")."`"."."."`".SLS_String::substrAfterLastDelimiter($clause[$i]["column"],".")."`";
					else
						$clause[$i]["column"] = "`".$clause[$i]["column"]."`";
					
					switch($clause[$i]["mode"])
					{
						case "like":
							$sql .= ("    ".$clause[$i]["column"]." LIKE ".$this->_db->quote("%".($clause[$i]["value"])."%"));
							break;
						case "notlike":
							$sql .= ("    ".$clause[$i]["column"]." NOT LIKE ".$this->_db->quote("%".($clause[$i]["value"])."%"));
							break;							
						case "beginwith":
							$sql .= ("    ".$clause[$i]["column"]." LIKE ".$this->_db->quote(($clause[$i]["value"])."%"));
							break;
						case "endwith":
							$sql .= ("    ".$clause[$i]["column"]." LIKE ".$this->_db->quote("%".($clause[$i]["value"])));
							break;
						case "equal":
							$sql .= ("    ".$clause[$i]["column"]." = ".$this->_db->quote($clause[$i]["value"]));	
							break;
						case "notequal":
							$sql .= ("    ".$clause[$i]["column"]." != ".$this->_db->quote($clause[$i]["value"]));	
							break;
						case "lt":
							$sql .= ("    ".$clause[$i]["column"]." < ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "le":
							$sql .= ("    ".$clause[$i]["column"]." <= ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "ge":
							$sql .= ("    ".$clause[$i]["column"]." >= ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "gt":
							$sql .= ("    ".$clause[$i]["column"]." > ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "null":
							$sql .= ("    ".$clause[$i]["column"]." IS NULL ");
							break;
						case "notnull":
							$sql .= ("    ".$clause[$i]["column"]." IS NOT NULL ");
							break;
						case "in":
							$clause[$i]["value"] = (is_array($clause[$i]["value"])) ? array_map(array($this->_db, 'quote'),$clause[$i]["value"]) : $clause[$i]["value"];
							$sql .= ("    ".$clause[$i]["column"]." IN (".((is_array($clause[$i]["value"]) ? implode(",",$clause[$i]["value"]) : $this->_db->quote($clause[$i]["value"]))).") ");
							break;
						case "notin":
							$clause[$i]["value"] = (is_array($clause[$i]["value"])) ? array_map(array($this->_db, 'quote'),$clause[$i]["value"]) : $clause[$i]["value"];
							$sql .= ("    ".$clause[$i]["column"]." NOT IN (".((is_array($clause[$i]["value"]) ? implode(",",$clause[$i]["value"]) : $this->_db->quote($clause[$i]["value"]))).") ");
							break;
						default:
							$sql .= ("    ".$clause[$i]["column"]." LIKE ".$this->_db->quote("%".($clause[$i]["value"])."%"));
							break;
					}						
					$lastSucceeded = true;
				}
				// Else, throw a Sls Exception
				else
					SLS_Tracing::addTrace(new Exception("Warning: Column ".$clause[$i]["column"]." in clause where doesn't exist in table `".$table."` (and collected tables to join)"),true);
			}
		}
		/**
		 * /CLAUSE
		 */
		
		
		/**
		 * GROUP BY
		 */
		// If we want a group by
		if (!empty($group) && is_array($group))
		{
			$lastSucceeded = false;
			// Foreach items to group
			foreach($group as $colGroup)			
			{					
				// If column on which you want to apply a group by statement is in the list of columns collected on the tables
				$columnName = (SLS_String::contains($colGroup,".")) ? SLS_String::substrAfterLastDelimiter($colGroup,".") : $colGroup;
				if (in_array($columnName,$columnsMain))
				{
					if (SLS_String::contains($colGroup,"."))
						$colGroup = "`".SLS_String::substrBeforeLastDelimiter($colGroup,".")."`"."."."`".SLS_String::substrAfterLastDelimiter($colGroup,".")."`";
					else
						$colGroup = "`".$colGroup."`";
					
					$sql .= ($lastSucceeded) ? ", "."\n" : "\n"."GROUP BY "."\n";
					$sql .= "    ".$colGroup." ";
					$lastSucceeded = true;
				}
				// Else, throw a Sls Exception
				else
					SLS_Tracing::addTrace(new Exception("Warning: Column `".$columnName."` in group by statement doesn't exist in table `".$table."` (and collected tables to join)"),true);
			}
		}
		/**
		 * /GROUP BY
		 */
		
		
		/**
		 * ORDER
		 */
		// If we haven't fill the order to apply, order by pk asc
		if (empty($order))
		{			
			// If the table want is different with current model, get the primary key of this table
			if ($this->_table != $table)							
				$pk = $object->getPrimaryKey();
			// Else, just get current pk
			else
				$pk = $this->_primaryKey;
			$order = array(array("column" => $pk, "order" => "asc"));			
		}
		// If we want to order by rand()
		if ($order == "rand()")
		{
			$sql .= "\n"."ORDER BY "."\n".
					"    rand() ";
		}
		// Else, construct the order statement
		else
		{
			$lastSucceeded = false;
			
			// Foreach items to order
			for($i=0 ; $i<$count=count($order) ; $i++)					
			{
				$columnName = (SLS_String::contains($order[$i]["column"],".")) ? SLS_String::substrAfterLastDelimiter($order[$i]["column"],".") : $order[$i]["column"];
				
				// If column on which you want to order is in the list of columns collected on the tables
				if (in_array($columnName,$columnsMain))
				{
					if (SLS_String::contains($order[$i]["column"],"."))
						$order[$i]["column"] = "`".SLS_String::substrBeforeLastDelimiter($order[$i]["column"],".")."`"."."."`".SLS_String::substrAfterLastDelimiter($order[$i]["column"],".")."`";
					else
						$order[$i]["column"] = "`".$order[$i]["column"]."`";
					
					$sql .= ($lastSucceeded) ? ", "."\n" : "\n"."ORDER BY "."\n";
					$sql .= "    ".$order[$i]["column"]."";
					$sql .= ($this->_db->validateAsc($order[$i]["order"])) ? " ".strtoupper($order[$i]["order"])." " : " ASC ";
					$lastSucceeded = true;
				}
				// Else, throw a Sls Exception
				else
					SLS_Tracing::addTrace(new Exception("Warning: Column `".$columnName."` in order by statement doesn't exist in table `".$table."` (and collected tables to join)"),true);
			}
		}
		/**
		 * /ORDER
		 */
		
		
		/**
		 * LIMIT
		 */
		// If we want to limit recordsets
		if (!empty($limit) && is_array($limit))
		{
			if (array_key_exists("start",$limit) && array_key_exists("length",$limit))
			{
				$start = $limit["start"];
				$length = $limit["length"];				
			}
			else
			{
				$start = array_shift(array_keys($limit));
				$length = array_shift(array_values($limit));
			}
			
			if (is_numeric($start) && is_numeric($length) && $start >= 0 && $length > 0)
				$sql .= "\n"."LIMIT "."\n".
						"    ".((empty($start)) ? $length : $start.",".$length);
		}
		/**
		 * /LIMIT
		 */		
		
		// Try to execute the built query
		try
		{			
			return $this->_db->select($sql);
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
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
		$allowToJoin = false;
		$modeJoin = "inner";
		$modesJoin = array("inner","left","right");
		$columns = array();
		
		/**
		 * TABLE NAME
		 */
		// If table name haven't been filled, try to recover table name from the current model
		if (empty($table))
		{
			$table = (empty($this->_table)) ? substr(get_class($this),0,strlen(get_class($this))-3) : $this->_table;
		}
		// If table name is again empty, throw a Sls Exception
		if (empty($table))
		{
			SLS_Tracing::addTrace(new Exception("Error: Table's name has been omitted"),true);
			return false;
		}
		// If model doesn't exists
		if ($this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"user") || $this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"sls"))
		{
			$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($table);
			$object = new $className();
			foreach($object->getParams() as $key => $value)
				array_push($columns,$key);
		}
		else
		{
			SLS_Tracing::addTrace(new Exception("Error: Table `".$table."` doesn't exist in database `".($this->_db->getCurrentDb())."`"),true);
			return false;
		}
		/**
		 * /TABLE NAME
		 */

		
		/**
		 * JOINS
		 */
		// Get all the columns of the current table
		$columnsMain = $columns;
		$joinMain = $table;
		
		// If we want to join tables
		if (is_array($joins) && !empty($joins))
		{
			// Foreach tables to join
			foreach ($joins as $currentJoin)
			{
				// If we want joins with the clause "using"
				if (is_array($currentJoin))
				{
					// Override join mode ?
					if (array_key_exists("mode",$currentJoin))					
						$modeJoin = (in_array(strtolower($currentJoin["mode"]),$modesJoin)) ? strtolower($currentJoin["mode"]) : array_shift($modesJoin);
				
					$currentJoin = $currentJoin["table"];
				}
				
				// If the table to join doesn't exists in MySQL, throw a Sls Exception
				if (!$this->_generic->useModel(SLS_String::tableToClass($currentJoin),$this->_db->getCurrentDb(),"user") && !$this->_generic->useModel(SLS_String::tableToClass($currentJoin),$this->_db->getCurrentDb(),"sls"))
					SLS_Tracing::addTrace(new Exception("Warning: Table `".$currentJoin."` to join with `".$joinMain."` doesn't exist in database `".(SLS_Generic::getInstance()->getDbConfig("base"))."`"),true);			
				// Else check if we can join
				else 
				{
					$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($currentJoin);
					$objectJoin = new $className();
					$columnsJoin = array();
					
					// Get all the columns of the table to join
					foreach($objectJoin->getParams() as $key => $value)
						array_push($columnsJoin,$key);
					
					// If we want joins with the clause "using", allow to join and merge the columns of the table to join with all the columns already listed
					if (is_array($currentJoin))
					{
						$allowToJoin = true;
						$columnsMain = array_merge($columnsMain,$columnsJoin);	
					}
					// Else if we want a "NATURAL JOIN", check if we have a common key
					else
					{
						// Foreach columns of the current table
						foreach($columnsMain as $tMain)
						{
							// Foreach columns of the table to join
							foreach($columnsJoin as $tJoin)
							{
								// If we have a common column, allow to join and merge the columns of the table to join with all the columns already listed
								if ($tJoin == $tMain)
								{
									$allowToJoin = true;
									$columnsMain = array_merge($columnsMain,$columnsJoin);
								}
							}
						}
					}
					
					// If we can't join, throw a Sls Exception
					if (!$allowToJoin)
					{
						SLS_Tracing::addTrace(new Exception("Warning: Table `".$currentJoin."` to join with `".$joinMain."` doesn't have a common key"),true);
						$joins = array();
						break;
					}
					
					// Move the reference table
					$joinMain = $currentJoin;
				}
			}
		}
		
		/**
		 * GROUP BY (COUNT DISTINCT group_column instead of *)
		 */
		// If we want a group by
		if (!empty($group) && is_array($group))
		{
			$groupSucceeded = false;
			$columnGroup = array_shift($group);
			// If column on which you want to apply a group by statement is in the list of columns collected on the tables
			$columnGroup = (SLS_String::contains($columnGroup,".")) ? SLS_String::substrAfterLastDelimiter($columnGroup,".") : $columnGroup;
			if (in_array($columnGroup,$columnsMain))
			{
				if (SLS_String::contains($columnGroup,"."))
					$columnGroup = "`".SLS_String::substrBeforeLastDelimiter($columnGroup,".")."`"."."."`".SLS_String::substrAfterLastDelimiter($columnGroup,".")."`";
				else
					$columnGroup = "`".$columnGroup."`";
				
				$groupSucceeded = true;
			}
			// Else, throw a Sls Exception
			else
				SLS_Tracing::addTrace(new Exception("Warning: Column `".$columnGroup."` in group by statement doesn't exist in table `".$table."` (and collected tables to join)"),true);
		}
		/**
		 * /GROUP BY
		 */
		
		// Build the start of the query
		$sql .= "SELECT COUNT(".(($groupSucceeded) ? "DISTINCT(".$columnGroup.") " : "*").") AS total FROM `".$table."` ";
				
		// If we can join, build the join
		if ($allowToJoin && is_array($joins) && !empty($joins))
		{
			foreach ($joins as $currentJoin)
			{
				if (is_array($currentJoin))
					$sql .= strtoupper($modeJoin)." JOIN "."\n".
					"    `".$currentJoin["table"]."` USING(".$currentJoin["column"].") ";
				else
					$sql .= "NATURAL JOIN "."\n".
					"    `".$currentJoin."` ";
			}
		}
		/**
		 * /JOINS
		 */
				
		
		/**
		 * CLAUSE
		 */
		// If we want a clause where
		if (!empty($clause))
		{			
			$lastSucceeded = false;
			// Foreach items to restrict
			for($i=0 ; $i<count($clause) ; $i++)
			{
				// If column on which you want to apply a restrict clause is in the list of columns collected on the tables
				$columnName = (SLS_String::contains($clause[$i]["column"],".")) ? SLS_String::substrAfterLastDelimiter($clause[$i]["column"],".") : $clause[$i]["column"];				
				if (in_array($columnName,$columnsMain))
				{
					$sql .= ($lastSucceeded) ? " AND " : " WHERE ";
					
					// Build the correct statement
					if (SLS_String::contains($clause[$i]["column"],"."))
						$clause[$i]["column"] = "`".SLS_String::substrBeforeLastDelimiter($clause[$i]["column"],".")."`"."."."`".SLS_String::substrAfterLastDelimiter($clause[$i]["column"],".")."`";
					else
						$clause[$i]["column"] = "`".$clause[$i]["column"]."`";
						
					switch($clause[$i]["mode"])
					{
						case "like":
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])."%"));
							break;
						case "notlike":
							$sql .= ("LOWER(".$clause[$i]["column"].") NOT LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])."%"));
							break;							
						case "beginwith":
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote(strtolower($clause[$i]["value"])."%"));
							break;
						case "endwith":
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])));
							break;
						case "equal":
							$sql .= ("".$clause[$i]["column"]." = ".$this->_db->quote($clause[$i]["value"]));	
							break;
						case "notequal":
							$sql .= ("".$clause[$i]["column"]." != ".$this->_db->quote($clause[$i]["value"]));	
							break;
						case "lt":
							$sql .= ("".$clause[$i]["column"]." < ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "le":
							$sql .= ("".$clause[$i]["column"]." <= ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "ge":
							$sql .= ("".$clause[$i]["column"]." >= ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "gt":
							$sql .= ("".$clause[$i]["column"]." > ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "null":
							$sql .= ("".$clause[$i]["column"]." IS NULL ");
							break;
						case "notnull":
							$sql .= ("".$clause[$i]["column"]." IS NOT NULL ");
							break;
						case "in":
							$clause[$i]["value"] = (is_array($clause[$i]["value"])) ? array_map(array($this->_db, 'quote'),$clause[$i]["value"]) : $clause[$i]["value"];
							$sql .= ("".$clause[$i]["column"]." IN (".((is_array($clause[$i]["value"]) ? implode(",",$clause[$i]["value"]) : $this->_db->quote($clause[$i]["value"]))).") ");
							break;
						case "notin":
							$clause[$i]["value"] = (is_array($clause[$i]["value"])) ? array_map(array($this->_db, 'quote'),$clause[$i]["value"]) : $clause[$i]["value"];
							$sql .= ("    ".$clause[$i]["column"]." NOT IN (".((is_array($clause[$i]["value"]) ? implode(",",$clause[$i]["value"]) : $this->_db->quote($clause[$i]["value"]))).") ");
							break;
						default:
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])."%"));
							break;
					}						
					$lastSucceeded = true;
				}
				// Else, throw a Sls Exception
				else
					SLS_Tracing::addTrace(new Exception("Warning: Column ".$clause[$i]["column"]." in clause where doesn't exist in table `".$table."` (and collected tables to join)"),true);
			}
		}
		/**
		 * /CLAUSE
		 */
		
		
		// Try to execute the built query
		try
		{			
	        $result =  array_shift($this->_db->select($sql));
	        return $result->total;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
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
	public function deleteModels($table,$joins,$clause)
	{
		$allowToJoin = false;
		$modeJoin = "inner";
		$modesJoin = array("inner","left","right");
		$columns = array();
		
		/**
		 * TABLE NAME
		 */
		// If table name haven't been filled, try to recover table name from the current model
		if (empty($table))
		{
			$table = (empty($this->_table)) ? substr(get_class($this),0,strlen(get_class($this))-3) : $this->_table;
		}
		// If table name is again empty, throw a Sls Exception
		if (empty($table))
		{
			SLS_Tracing::addTrace(new Exception("Error: Table's name has been omitted"),true);
			return false;
		}
		// If model doesn't exists
		if ($this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"user") || $this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"sls"))
		{
			$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($table);
			$object = new $className();
			foreach($object->getParams() as $key => $value)
				array_push($columns,$key);
		}
		else
		{
			SLS_Tracing::addTrace(new Exception("Error: Table `".$table."` doesn't exist in database `".($this->_db->getCurrentDb())."`"),true);
			return false;
		}
		/**
		 * /TABLE NAME
		 */

		
		/**
		 * JOINS
		 */
		// Get all the columns of the current table
		$columnsMain = $columns;
		$joinMain = $table;
		
		// If we want to join tables
		if (is_array($joins) && !empty($joins))
		{
			// Foreach tables to join
			foreach ($joins as $currentJoin)
			{
				// If we want joins with the clause "using"
				if (is_array($currentJoin))
				{
					// Override join mode ?
					if (array_key_exists("mode",$currentJoin))					
						$modeJoin = (in_array(strtolower($currentJoin["mode"]),$modesJoin)) ? strtolower($currentJoin["mode"]) : array_shift($modesJoin);
					
					$currentJoin = $currentJoin["table"];
				}
				
				// If the table to join doesn't exists in MySQL, throw a Sls Exception
				if (!$this->_generic->useModel(SLS_String::tableToClass($currentJoin),$this->_db->getCurrentDb(),"user") && !$this->_generic->useModel(SLS_String::tableToClass($currentJoin),$this->_db->getCurrentDb(),"sls"))
					SLS_Tracing::addTrace(new Exception("Warning: Table `".$currentJoin."` to join with `".$joinMain."` doesn't exist in database `".(SLS_Generic::getInstance()->getDbConfig("base"))."`"),true);			
				// Else check if we can join
				else 
				{
					$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($currentJoin);
					$objectJoin = new $className();
					$columnsJoin = array();
					
					// Get all the columns of the table to join
					foreach($objectJoin->getParams() as $key => $value)
						array_push($columnsJoin,$key);
					
					// If we want joins with the clause "using", allow to join and merge the columns of the table to join with all the columns already listed
					if (is_array($currentJoin))
					{
						$allowToJoin = true;
						$columnsMain = array_merge($columnsMain,$columnsJoin);	
					}
					// Else if we want a "NATURAL JOIN", check if we have a common key
					else
					{
						// Foreach columns of the current table
						foreach($columnsMain as $tMain)
						{
							// Foreach columns of the table to join
							foreach($columnsJoin as $tJoin)
							{
								// If we have a common column, allow to join and merge the columns of the table to join with all the columns already listed
								if ($tJoin == $tMain)
								{
									$allowToJoin = true;
									$columnsMain = array_merge($columnsMain,$columnsJoin);
								}
							}
						}
					}
					
					// If we can't join, throw a Sls Exception
					if (!$allowToJoin)
					{
						SLS_Tracing::addTrace(new Exception("Warning: Table `".$currentJoin."` to join with `".$joinMain."` doesn't have a common key"),true);
						$joins = array();
						break;
					}
					
					// Move the reference table
					$joinMain = $currentJoin;
				}
			}
		}
		
		// Build the start of the query
		$sql = "DELETE `".$table."` FROM `".$table."` ";
		
		// If we can join, build the join
		if ($allowToJoin && is_array($joins) && !empty($joins))
		{
			foreach ($joins as $currentJoin)
			{
				if (is_array($currentJoin))
					$sql .= strtoupper($modeJoin)." JOIN "."\n".
					"    `".$currentJoin["table"]."` USING(".$currentJoin["column"].") ";
				else
					$sql .= "NATURAL JOIN "."\n".
					"    `".$currentJoin."` ";
			}
		}
		/**
		 * /JOINS
		 */
				
		
		/**
		 * CLAUSE
		 */
		// If we want a clause where
		if (!empty($clause))
		{			
			$lastSucceeded = false;
			// Foreach items to restrict
			for($i=0 ; $i<count($clause) ; $i++)
			{
				// If column on which you want to apply a restrict clause is in the list of columns collected on the tables
				$columnName = (SLS_String::contains($clause[$i]["column"],".")) ? SLS_String::substrAfterLastDelimiter($clause[$i]["column"],".") : $clause[$i]["column"];				
				if (in_array($columnName,$columnsMain))
				{
					$sql .= ($lastSucceeded) ? " AND " : " WHERE ";
					
					// Build the correct statement
					$clause[$i]["column"] = "`".$clause[$i]["column"]."`";
					switch($clause[$i]["mode"])
					{
						case "like":
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])."%"));
							break;
						case "notlike":
							$sql .= ("LOWER(".$clause[$i]["column"].") NOT LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])."%"));
							break;							
						case "beginwith":
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote(strtolower($clause[$i]["value"])."%"));
							break;
						case "endwith":
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])));
							break;
						case "equal":
							$sql .= ("".$clause[$i]["column"]." = ".$this->_db->quote($clause[$i]["value"]));	
							break;
						case "notequal":
							$sql .= ("".$clause[$i]["column"]." != ".$this->_db->quote($clause[$i]["value"]));	
							break;
						case "lt":
							$sql .= ("".$clause[$i]["column"]." < ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "le":
							$sql .= ("".$clause[$i]["column"]." <= ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "ge":
							$sql .= ("".$clause[$i]["column"]." >= ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "gt":
							$sql .= ("".$clause[$i]["column"]." > ".$this->_db->quote($clause[$i]["value"]));
							break;
						case "null":
							$sql .= ("".$clause[$i]["column"]." IS NULL ");
							break;
						case "notnull":
							$sql .= ("".$clause[$i]["column"]." IS NOT NULL ");
							break;
						case "in":
							$clause[$i]["value"] = (is_array($clause[$i]["value"])) ? array_map(array($this->_db, 'quote'),$clause[$i]["value"]) : $clause[$i]["value"];
							$sql .= ("".$clause[$i]["column"]." IN (".((is_array($clause[$i]["value"]) ? implode(",",$clause[$i]["value"]) : $this->_db->quote($clause[$i]["value"]))).") ");
							break;
						case "notin":
							$clause[$i]["value"] = (is_array($clause[$i]["value"])) ? array_map(array($this->_db, 'quote'),$clause[$i]["value"]) : $clause[$i]["value"];
							$sql .= ("    ".$clause[$i]["column"]." NOT IN (".((is_array($clause[$i]["value"]) ? implode(",",$clause[$i]["value"]) : $this->_db->quote($clause[$i]["value"]))).") ");
							break;
						default:
							$sql .= ("LOWER(".$clause[$i]["column"].") LIKE ".$this->_db->quote("%".strtolower($clause[$i]["value"])."%"));
							break;
					}						
					$lastSucceeded = true;
				}
				// Else, throw a Sls Exception
				else
					SLS_Tracing::addTrace(new Exception("Warning: Column ".$clause[$i]["column"]." in clause where doesn't exist in table `".$table."` (and collected tables to join)"),true);
			}
		}
		/**
		 * /CLAUSE
		 */
				
		
		// Try to execute the built query
		try
		{
			return $this->_db->delete($sql);		
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Give the next id from the current model
	 *
	 * @access public
	 * @return array PDO
	 * @since 1.0
	 */
	public function giveNextId()
	{		
		$sql = "SELECT "."\n".
				"    `".$this->_primaryKey."` AS nextId "."\n"."
				FROM "."\n".
				"    `".$this->_table."` "."\n".
				"ORDER BY "."\n".
				"    `".$this->_primaryKey."` DESC "."\n".
				"LIMIT "."\n".
				"    1";
		try
		{			
			return array_shift($this->_db->select($sql));			
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}		
	}
	
	/**
	 * Get the comment of a table
	 *
	 * @access public	 
	 * @param string $table the table name
	 * @param string $db the db alias (if empty, current db)
	 * @return string $comment the comment on the table
	 * @see SLS_FrontModelSql::setTableComment
	 * @see SLS_FrontModelSql::getColumnComment
	 * @see SLS_FrontModelSql::setColumnComment
	 * @since 1.0
	 */
	public function getTableComment($table,$db)
	{
		$comment = $table;

		// If need to switch db
		if ($this->_db->getCurrentDb() != $db)
			$this->_db->changeDb($db);
				
		$infos = $this->_db->showTables($table);				
		if (!empty($infos->Comment))
			$comment = $infos->Comment;
		
		return $comment;
	}
	
	/**
	 * Set the comment of a table
	 *
	 * @access public	 
	 * @param string $comment the comment to save
	 * @param string $table the table name
	 * @param string $db the db alias (if empty, current db)
	 * @return bool true if saved, else false
	 * @see SLS_FrontModelSql::getTableComment
	 * @see SLS_FrontModelSql::getColumnComment
	 * @see SLS_FrontModelSql::setColumnComment
	 * @since 1.0
	 */
	public function setTableComment($comment,$table,$db)
	{
		// If need to switch db
		if ($this->_db->getCurrentDb() != $db)
			$this->_db->changeDb($db);
			
		$comment = str_replace("'","''",$comment);			
		$sql = "ALTER TABLE "."\n".
				"    `".$table."` "."\n".
				"COMMENT = '".$comment."'";			
		return $this->_db->update($sql);				
	}
	
	/**
	 * Get the comment of a column
	 *
	 * @access public
	 * @param string $column the column name
	 * @param string $table the table name
	 * @param string $db the db alias (if empty, current db)
	 * @return string $comment the comment on a column
	 * @since 1.0
	 */
	public function getColumnComment($column,$table,$db)
	{
		$comment = $column;
		
		// If need to switch db
		if ($this->_db->getCurrentDb() != $db)
			$this->_db->changeDb($db);
		
		$columns = $this->_db->showColumns($table);
		
		for($i=0 ; $i<$count=count($columns) ; $i++)		
			if ($columns[$i]->Field == $column)
				$comment = $columns[$i]->Comment;		
		
		return $comment;
	}
	
	/**
	 * Set the comment of a column
	 *
	 * @access public
	 * @param string $column the column name
	 * @param string $comment the comment to save
	 * @param string $table the table name
	 * @param string $db the db alias (if empty, current db)
	 * @return bool true if saved, else false
	 * @see SLS_FrontModelSql::getTableComment
	 * @see SLS_FrontModelSql::getColumnComment
	 * @see SLS_FrontModelSql::getColumnComment
	 * @since 1.0
	 */
	public function setColumnComment($column,$comment,$table,$db)
	{
		// If need to switch db
		if ($this->_db->getCurrentDb() != $db)
			$this->_db->changeDb($db);
		
		$type = "";
		$cols = $this->_db->showColumns($table);
		$columns = array();
		for($i=0 ; $i<$count=count($cols) ; $i++)
			array_push($columns,$cols[$i]->Field);
		
		$comment = str_replace("'","''",$comment);
		
		// If column doesn't exists in this table
		if (!in_array($column,$columns))
			return false;
		
		// Search the column type
		for($i=0 ; $i<$count=count($cols) ; $i++)		
			if ($cols[$i]->Field == $column)
			{
				$type = $cols[$i]->Type;
				$null = ($cols[$i]->Null=="NO") ? "NOT NULL" : "NULL";
				$ai = ($cols[$i]->Extra=="auto_increment") ? "AUTO_INCREMENT" : "";
			}
		
		// If the column type doesn't found
		if (empty($type) || empty($null))
			return false;		
		else
		{			
			$sql = "ALTER TABLE "."\n".
					"    `".$table."` "."\n".
					"MODIFY "."\n".
					"    `".$column."` ".$type." ".$null." ".$ai." COMMENT '".$comment."'";
			$this->_db->update($sql);
			return true;
		}
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
		// If need to switch db
		if ($this->_db->getCurrentDb() != $db)
			$this->_db->changeDb($db);
			
		$sql = "SELECT DEFAULT(`".$column."`) AS default_value "."\n".
				"FROM "."\n".
				"    `".$table."` "."\n".
				"LIMIT "."\n".
				"    1";
		try 
		{			
			$result = array_shift($this->_db->select($sql));
			return $result->default_value;
		}
		catch (Exception $e)
		{
			return null;
		}	
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
	public function isUnique($column,$value,$table,$excludedColumn="",$excludedValue="")
	{
		$sql = "SELECT "."\n".
				"    COUNT(*) AS total "."\n".
				"FROM "."\n".
				"    `".$this->_table."` "."\n".
				"WHERE "."\n".
				"    `".$column."` = ".$this->_db->quote($value)." "."\n";
		if ($this->_isMultilanguage)
			$sql .= "    AND `pk_lang` = ".$this->_db->quote($this->_modelLanguage)." "."\n";
		if (!empty($excludedColumn) && !empty($excludedValue))		
			$sql .= "    AND `".$excludedColumn."` != ".$this->_db->quote($excludedValue)." "."\n";		
		try
		{
			$result =  array_shift($this->_db->select($sql));
			return $result->total;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Get foreign key recordsets of a given table 
	 * 
	 * @param string $tableSource
	 * @param string $tableFk
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
		$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."fks.xml"));
		$currentDb = $this->_db->getCurrentDb();
		
		if (!empty($id))
		{
			$sql = "SELECT * FROM ";
			for($i=0 ; $i<$count=count($tableFk) ; $i++)
			{
				if(!$this->_db->tableExists($tableFk[$i]))
					continue;
				
				if ($i> 0)
				{
					$currentFkTable = $tableFk[$i];
					$sourceFkTable = ($i==0) ? $tableSource : $tableFk[0];
					$columnFk = $xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($currentDb."_".$sourceFkTable)."' and @tablePk='".strtolower($currentDb)."_".SLS_String::tableToClass($currentFkTable)."']/@columnFk");
					
					$this->_generic->useModel(SLS_String::tableToClass($currentFkTable), $currentDb);
					$currentFkClass = ucfirst(strtolower($currentDb))."_".SLS_String::tableToClass($currentFkTable);
					$currentFkObject = new $currentFkClass();
					$sql .= "LEFT JOIN `".$tableFk[$i]."` ";
				}
				
				$sql .= "`".$tableFk[$i]."` ";
				
				if ($i > 0 && !empty($columnFk))
				{
					$sql .= "ON `".$tableFk[0]."`.`".$columnFk."` = `".$tableFk[$i]."`.`".$currentFkObject->getPrimaryKey()."`  ";
				}
			}
			$tableAlias = (!empty($tableFk)) ? "`".$tableFk[0]."`." : "";
			$sql .= "WHERE ".$tableAlias."`".$pkFk."` = ".$id." ";
			if (!empty($lang))
				$sql .= "AND ".$tableAlias."`pk_lang` = ".$this->_db->quote($lang)." ";
			$sql .= "GROUP BY ".$tableAlias."`".$pkFk."` ORDER BY ".$clause." ASC LIMIT 0,".$nb;
			$results = $this->_db->select($sql);
		}
		else
		{
			if (empty($clause) || empty($value))
			{
				$sql = "SELECT COUNT(*) AS total, ".implode(',',$columnsToSelect)." FROM `".$tableSource."` ";
				for($i=0 ; $i<$count=count($tableFk) ; $i++)
				{
					if(!$this->_db->tableExists($tableFk[$i]))
						continue;
					
					$currentFkTable = $tableFk[$i];
					$sourceFkTable = ($i==0) ? $tableSource : $tableFk[0];
					$columnFk = $xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($currentDb."_".$sourceFkTable)."' and @tablePk='".strtolower($currentDb)."_".SLS_String::tableToClass($currentFkTable)."']/@columnFk");
					
					$this->_generic->useModel(SLS_String::tableToClass($currentFkTable), $currentDb);
					$currentFkClass = ucfirst(strtolower($currentDb))."_".SLS_String::tableToClass($currentFkTable);
					$currentFkObject = new $currentFkClass();
					$sql .= "LEFT JOIN `".$tableFk[$i]."` ON `".$sourceFkTable."`.`".$columnFk."` = `".$tableFk[$i]."`.`".$currentFkObject->getPrimaryKey()."` ";
				}
				
				$tableAlias = (!empty($tableFk)) ? "`".$tableFk[0]."`." : "";
				if (!empty($lang))
					$sql .= "WHERE ".$tableAlias."`pk_lang` = ".$this->_db->quote($lang)." ";
				$sql .= "GROUP BY ".$tableAlias."`".$pkFk."` LIMIT 0,".$nb;
				
				$results = $this->_db->select($sql);
				$nbResults = count($results);
				if ($nbResults < $nb && count($tableFk) > 0)
				{
					$pks = array();
					for($i=0 ; $i<$count=count($results) ; $i++)
						$pks[] = $results[$i]->{$pkFk};
					$table = array_shift($tableFk);
					$sql = "SELECT 0 AS total, ".implode(',',$columnsToSelect)." FROM `".$table."` ";
					for($i=0 ; $i<$count=count($tableFk) ; $i++)
					{
						if(!$this->_db->tableExists($tableFk[$i]))
							continue;
						
						$currentFkTable = $tableFk[$i];
						$sourceFkTable = $table;
						$columnFk = $xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($currentDb."_".$sourceFkTable)."' and @tablePk='".strtolower($currentDb)."_".SLS_String::tableToClass($currentFkTable)."']/@columnFk");
						
						$this->_generic->useModel(SLS_String::tableToClass($currentFkTable), $currentDb);
						$currentFkClass = ucfirst(strtolower($currentDb))."_".SLS_String::tableToClass($currentFkTable);
						$currentFkObject = new $currentFkClass();
						$sql .= "LEFT JOIN `".$tableFk[$i]."` ON `".$sourceFkTable."`.`".$columnFk."` = `".$tableFk[$i]."`.`".$currentFkObject->getPrimaryKey()."` ";
					}
					$tableAlias = (!empty($table)) ? "`".$table."`." : "";
					$sql .= "WHERE 1=1 ";
					if (!empty($lang))
						$sql .= "AND ".$tableAlias."`pk_lang` = ".$this->_db->quote($lang)." ";
					if (!empty($pks))
						$sql .= "AND ".$tableAlias."`".$pkFk."` NOT IN (".implode(',',$pks).") ";
					$sql .= "GROUP BY ".$tableAlias."`".$pkFk."` LIMIT 0,".($nb - $nbResults);
					$results = array_merge($results,$this->_db->select($sql));
				}
			}
			else
			{
				$sql = "SELECT * FROM ";
				for($i=0 ; $i<$count=count($tableFk) ; $i++)
				{
					if(!$this->_db->tableExists($tableFk[$i]))
						continue;
					
					if ($i> 0)
					{
						$currentFkTable = $tableFk[$i];
						$sourceFkTable = $tableFk[0];
						$columnFk = $xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($currentDb."_".$sourceFkTable)."' and @tablePk='".strtolower($currentDb)."_".SLS_String::tableToClass($currentFkTable)."']/@columnFk");
						$sql .= (!empty($columnFk)) ? "LEFT JOIN " : "NATURAL JOIN ";
					}
					
					$sql .= "`".$tableFk[$i]."` ";
					
					if ($i > 0 && !empty($columnFk))
					{
						$this->_generic->useModel(SLS_String::tableToClass($currentFkTable), $currentDb);
						$currentFkClass = ucfirst(strtolower($currentDb))."_".SLS_String::tableToClass($currentFkTable);
						$currentFkObject = new $currentFkClass();
						$sql .= "ON `".$tableFk[0]."`.`".$columnFk."` = `".$tableFk[$i]."`.`".$currentFkObject->getPrimaryKey()."` ";
					}
				}
				$tableAlias = (!empty($tableFk)) ? "`".$tableFk[0]."`." : "";
				$sql .= "WHERE ".$clause." LIKE LOWER(".$this->_db->quote("%".$value."%").") ";
				if (!empty($lang))
					$sql .= "AND ".$tableAlias."`pk_lang` = ".$this->_db->quote($lang)." ";
				$sql .= "GROUP BY ".$tableAlias."`".$pkFk."` ORDER BY ".$clause." ASC LIMIT 0,".$nb;
				$results = $this->_db->select($sql);
			}
		}
		return $results;
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
		return $this->_db->exec($exec);
	}
	
	/**
	 * Begin Transaction
	 * 
	 * @since 1.0.6
	 */
	public function beginTransaction()
	{
		return $this->_db->beginTransaction();
	}
	
	/**
	 * Commit Transaction
	 * 
	 * @since 1.0.6
	 */
	public function commitTransaction()
	{
		return $this->_db->commitTransaction();
	}
	
	/**
	 * Rollback Transaction
	 * 
	 * @since 1.0.6
	 */
	public function rollbackTransaction()
	{
		return $this->_db->rollbackTransaction();
	}
}
?>