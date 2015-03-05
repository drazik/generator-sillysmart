<?php
/**
 * SQL_Sql class - SQL Mother
 *  
 * @author Laurent Bientz
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Sql
 * @since 1.0 
 */
class SLS_Sql 
{
	private static $_instance;
	private $_dbh = array();
	private $_currentDb = "";
	private $_generic;
	private $_cache;
	private $_tables = array();	
	public $_explain = true;

	/**
	 * Constructor, instanciate PDO class to prepare connection(s)
	 * 
	 * @access public
	 * @since 1.0
	 */
	public function __construct() 
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_cache = $this->_generic->getObjectCache();
		$needed = array("mysql", "PDO", "pdo_mysql");
		$php = get_loaded_extensions();
		
		foreach ($needed as $phpPre)		
			if (!in_array($phpPre, $php))
				SLS_Tracing::addTrace(new Exception("You need PHP Extension : ".$phpPre));
	}
	
	/**
	 * Singleton 
	 *
	 * @access public static
	 * @return SLS_Sql $instance SLS_Sql instance
	 * @since 1.0
	 */
	public static function getInstance() 
	{
		if (is_null(self::$_instance)) 		
			self::$_instance = new SLS_Sql();		
		return self::$_instance;
	}
	
	/**
	 * Return all db alias
	 *
	 * @access public	 
	 * @return array $dbs all db alias
	 * @see SLS_Sql::getCurrentDb
	 * @see SLS_Sql::changeDb
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Sql::getInstance()->getDbs());
	 * // will produce :
	 * array("db1","..","dbN")
	 */
	public function getDbs()
	{
		$dbs = array();
		$results = $this->_generic->getDbXML()->getTagsAttribute("//dbs/db","alias");
		foreach($results as $result)
			array_push($dbs,$result["attribute"]);
		return $dbs;
	}
	
	/**
	 * Return the current db alias
	 *
	 * @access public
	 * @return string $db the current db
	 * @see SLS_Sql::getDbs
	 * @see SLS_Sql::changeDb
	 * @since 1.0
	 */
	public function getCurrentDb()
	{
		$defaultDb = array_shift($this->_generic->getDbXML()->getTags("//dbs/db[@isDefault='true']/@alias"));
		return (empty($this->_currentDb)) ? $defaultDb : $this->_currentDb;
	}
	
	/**
	 * Change the current database on which PDO is connected
	 *
	 * @access public
	 * @param string $db alias db name
	 * @return bool $changed true if database changed, else false
	 * @see SLS_Sql::getCurrentDb
	 * @see SLS_Sql::getDbs
	 * @since 1.0
	 */
	public function changeDb($db)
	{
		// Get all databases
		$dbs = $this->getDbs();
		
		$db = strtolower($db);
		
		// If db doesn't exist
		if (!in_array($db,$dbs))
			return false;			
		else
		{
			if (!empty($this->_dbh) && $db == $this->getCurrentDb())
			{
				$this->_currentDb = $db;
				return true;
			}
			
			// If PDO reference doesn't already exist
			if (!array_key_exists($db,$this->_dbh))
			{
				$dsn = "mysql:host=".$this->_generic->getDbConfig("host",$db).";dbname=".$this->_generic->getDbConfig("base",$db);
				try
				{
					$this->_generic->_time_checkpoint = microtime(true);
					$this->_dbh[$db] = new PDO($dsn, $this->_generic->getDbConfig("user",$db), $this->_generic->getDbConfig("pass",$db));
					$this->_dbh[$db]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);					
					$this->_currentDb = $db;
					$this->setCharset($db);
					$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Connecting","Host: ".$this->_generic->getDbConfig("host",$db)."\nDb: ".$this->_generic->getDbConfig("base",$db),"MySQL Query");
				}
				catch(Exception $e)
				{
					SLS_Tracing::addTrace($e,true);
					return false;
				}
			}
			else
            {
            	$this->_currentDb = $db;
            }
            
            $this->_tables = $this->getTables();
            
			return true;
		}
	}
	
	/**
	 * Set the charset for the current connection
	 *
	 * @param string $db the db alias
	 */
	public function setCharset($db)
	{		
		$result = array_shift($this->_generic->getDbXML()->getTagsAttribute("//dbs/db[@alias='".$db."']","charset"));
		if (!empty($result["attribute"]))		
			$this->exec("SET NAMES '".$result["attribute"]."';");
	}
	
	/**
	 * Ping a MySQL Connection
	 *
	 * @access public
	 * @param string $host the host
	 * @param string $db the db
	 * @param string $user the username
	 * @param string $pass the userpassword
	 * @return mixed $success true if connection succeed, else error message
	 * @since 1.0
	 */
	public function pingConnection($host,$db,$user,$pass)
	{		
		try
		{
			$pdo = new PDO("mysql:host=".$host.";dbname=".$db, $user, $pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return true;
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	/**
	 * Sql Select	 
	 *
	 * @access public
	 * @param string $query the sql query
	 * @return array $results array of PDO objects
	 * @see SLS_Sql::insert
	 * @see SLS_Sql::update
	 * @see SLS_Sql::delete
	 * @since 1.0
	 */
	public function select($query) 
	{
		if (SLS_String::startsWith(strtolower(trim($query)),"select"))
		{
			$this->logDependencies($query);
			$this->logExplains($query);
		}
		
		if (!$this->checkConnexion())
			return array();		
		try
		{
			$this->_generic->_time_checkpoint = microtime(true);
			$statement = $this->_dbh[$this->_currentDb]->query($query);
			$table = array();
			while ($result = $statement->fetchObject()) 		
				$table[] = $result;
			if (!SLS_String::startsWith(strtolower(trim($query)),"explain") && !SLS_String::startsWith(strtolower(trim($query)),"show"))
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query SELECT","Query: ".$query,"MySQL Query");
			return $table;
		}
		catch (Exception $e)
		{
			if (!SLS_String::startsWith(strtoupper(trim($query)),"SELECT DEFAULT") && !SLS_String::startsWith(strtoupper(trim($query)),"EXPLAIN SELECT DEFAULT"))
				SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Sql Insert
	 *
	 * @access public
	 * @param string $insert the sql query
	 * @return int $insert_id the last insert id
	 * @see SLS_Sql::select
	 * @see SLS_Sql::update
	 * @see SLS_Sql::delete
	 * @since 1.0
	 */
	public function insert($insert) 
	{		
		$this->flushCache($insert,"into");
		
		if (!$this->checkConnexion())
			return false;
		try
		{
			$this->_generic->_time_checkpoint = microtime(true);
			$this->_dbh[$this->_currentDb]->exec($insert);
			if (!SLS_String::startsWith(strtolower(trim($insert)),"explain"))
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query INSERT","Query: ".$insert,"MySQL Query");
			return $this->_dbh[$this->_currentDb]->lastInsertId();		
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Sql Update
	 *
	 * @access public
	 * @param string $update the sql query
	 * @return int $count the number of lines updated
	 * @see SLS_Sql::select
	 * @see SLS_Sql::insert
	 * @see SLS_Sql::delete
	 * @since 1.0
	 */
	public function update($update) 
	{		
		$this->flushCache($update,"update");
		
		if (!$this->checkConnexion())
			return false;		
		try
		{
			$this->_generic->_time_checkpoint = microtime(true);
			$count = $this->_dbh[$this->_currentDb]->exec($update);
			if (!SLS_String::startsWith(strtolower(trim($update)),"explain"))
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query UPDATE: ","Query: ".$update,"MySQL Query");
			return $count;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Sql Exec
	 *
	 * @access public
	 * @param string $exec the sql query
	 * @return int $count the number of lines updated/deleted
	 * @see SLS_Sql::select
	 * @see SLS_Sql::insert
	 * @see SLS_Sql::delete
	 * @see SLS_Sql::update
	 * @since 1.0
	 */
	public function exec($exec) 
	{
		if (!$this->checkConnexion())
			return false;		
		try
		{
			$this->_generic->_time_checkpoint = microtime(true);
			$result = $this->_dbh[$this->_currentDb]->exec($exec);
			if (!SLS_String::startsWith(strtolower(trim($exec)),"explain"))
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query EXEC","Query: ".$exec,"MySQL Query");
			return $result;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Sql Delete
	 *
	 * @access public
	 * @param string $delete the sql query
	 * @return mixed nb recordsets deleted if success, else false
	 * @see SLS_Sql::select
	 * @see SLS_Sql::insert
	 * @see SLS_Sql::update
	 * @since 1.0
	 */
	public function delete($delete) 
	{		
		$this->flushCache($delete,"from");
		
		if (!$this->checkConnexion())
			return false;		
		try
		{
			$this->_generic->_time_checkpoint = microtime(true);
			$count = $this->_dbh[$this->_currentDb]->exec($delete);
			if (!SLS_String::startsWith(strtolower(trim($delete)),"explain"))
				$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query DELETE","Query: ".$delete,"MySQL Query");
			return $count;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Log MySQL explains on select queries 
	 *	 
	 * @access private
	 * @param string $query the select to explain
	 * @since 1.0.9
	 */
	private function logExplains($query)
	{
		// If in prod mode or customer bo or explain disabled, don't log explains		
		if ($this->_generic->isProd() || $this->_generic->isBo() || !$this->_explain)
			return true;
		
		// Objects
		$select = strtolower($query);
		$logs = array();
		
		// Force explain on the query to grep tables and warning
		$explains = $this->select("EXPLAIN ".$select);
		
		for($i=0 ; $i<$count=count($explains) ; $i++)
		{
			$table = $explains[$i]->table;
			$type = $explains[$i]->type;
			$key = $explains[$i]->key;
			$keys = $explains[$i]->possible_keys;
			$extra = $explains[$i]->Extra;
			
			if (!in_array($table,$this->_tables))
			{				
				if (SLS_String::endsWith($select," ".$table))				
					$table = str_replace('`','',SLS_String::substrAfterLastDelimiter(trim(SLS_String::substrBeforeLastDelimiter($select," ".$table))," "));				
				else	
					$table = str_replace('`','',SLS_String::substrAfterLastDelimiter(trim(SLS_String::substrBeforeFirstDelimiter($select," ".$table." "))," "));
				if (!in_array($table,$this->_tables))
					continue;
			}
			
			$allowedExtras = array('using filesort',
								   'using temporary; using filesort');
			
			if ($type == 'ALL' && !empty($extra) && !in_array(strtolower($extra),$allowedExtras) && empty($keys) && empty($key))
				SLS_Tracing::addTrace(new Exception("Warning: FULL TABLE SCAN SELECT on table `".$this->_generic->getDbConfig("base",$this->getCurrentDb())."`.`".$table."`"." (<u>Extra:</u> '".$extra."' | <u>Possible keys:</u> '".((empty($keys)) ? "NULL" : $keys)."' | <u>Key:</u> NULL)"),true,"<div style=\"margin: 0 30px;padding: 10px;\"><pre name=\"code\" class=\"brush:sql\">".$query."</pre></div>");
		}		
	}
	
	/**
	 * Bind dependencies between mysql table and static|component|controller|action into controller_bind.json.
	 * This file is used to know if we need to flush cached files when we insert/update/delete datas into tables.
	 * 
	 * @access private
	 * @param string $query the sql select query you have to analyze
	 * @since 1.0.9
	 */
	private function logDependencies($query)
	{
		// If cache enabled, don't log dependencies
		if ($this->_generic->isCache())
			return true;
		
		// Objects
		$log = false;
		$query = strtolower($query);
		$tables = array();
				
		// Force explain on the query to grep tables
		$explains = $this->select("EXPLAIN ".$query);		
		for($i=0 ; $i<$count=count($explains) ; $i++)
			$tables[] = $explains[$i]->table;
		
		// Check all tables > real table name or fuckin alias ?
		for($i=0 ; $i<$count=count($tables) ; $i++)
		{		
			if (!in_array($tables[$i],$this->_tables))
			{				
				if (SLS_String::endsWith($query," ".$tables[$i]))				
					$tables[$i] = str_replace('`','',SLS_String::substrAfterLastDelimiter(trim(SLS_String::substrBeforeLastDelimiter($query," ".$tables[$i]))," "));				
				else	
					$tables[$i] = str_replace('`','',SLS_String::substrAfterLastDelimiter(trim(SLS_String::substrBeforeFirstDelimiter($query," ".$tables[$i]." "))," "));
				
					
				if (!in_array($tables[$i],$this->_tables))
					unset($tables[$i]);
			}
		}
		
		if (!empty($tables))
		{			
			$traces = debug_backtrace();		
			for($i=0 ; $i<$count=count($traces) ; $i++)
			{
				$file = $traces[$i]["file"];
				if (SLS_String::contains($file,$this->_generic->getPathConfig("staticsControllers")))
				{
					$log = true;
					$name = strtolower(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($file,$this->_generic->getPathConfig("staticsControllers")),".controller.php"));
					foreach($tables as $table)
						$this->_cache->addBind($table,"statics",$name);
					break;
				}
				if (SLS_String::contains($file,$this->_generic->getPathConfig("componentsControllers")))
				{
					$log = true;					 
					$name = strtolower(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($file,$this->_generic->getPathConfig("componentsControllers")),".controller.php"));
					foreach($tables as $table)
						$this->_cache->addBind($table,"components",$name);
					break;
				}
				if (SLS_String::contains($file,$this->_generic->getPathConfig("actionsControllers")))
				{
					$log = true;
					if (SLS_String::contains($file,"/__") && SLS_String::contains($file,".protected.php"))
					{
						$name = strtolower(SLS_String::substrAfterFirstDelimiter($this->_generic->getControllerId(),"_"));
						foreach($tables as $table)
							$this->_cache->addBind($table,"controllers",$name);
						break;
					}
					else
					{
						$name = strtolower(SLS_String::substrAfterFirstDelimiter($this->_generic->getActionId(),"_"));
						foreach($tables as $table)
							$this->_cache->addBind($table,"actions",$name);
						break;
					}
				}
			}
			if ($log)			
				$this->_cache->saveBind();				
		}
	}
	
	/**
	 * Flush cache
	 * 
	 * @access private
	 * @param string $query the sql query you have to analyze
	 * @param string $delimiter the delimiter after table name
	 * @since 1.0.9
	 */
	private function flushCache($query,$delimiter="update")
	{		
		$query = strtolower($query);
		$table = str_replace('`','',SLS_String::substrBeforeFirstDelimiter(trim(SLS_String::substrAfterFirstDelimiter($query,$delimiter))," "));
		
		if ($this->tableExists($table))		
			$this->_cache->flushFromTable($table);		
	}
		
	/**
	 * Begin Transaction
	 * 
	 * @access public
	 * @return bool true if transaction started, else false
	 * @since 1.0.6
	 */
	public function beginTransaction()
	{
		if (!$this->checkConnexion())
			return false;		
		try
		{
			$this->_dbh[$this->_currentDb]->beginTransaction();
			return true;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Commit Transaction
	 * 
	 * @access public
	 * @return bool true if transaction commited, else false
	 * @since 1.0.6
	 */
	public function commitTransaction()
	{
		if (!$this->checkConnexion())
			return false;		
		try
		{
			$this->_dbh[$this->_currentDb]->commit();
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Rollback Transaction
	 * 
	 * @access public
	 * @return bool true if transaction rollbacked, else false
	 * @since 1.0.6
	 */
	public function rollbackTransaction()
	{
		if (!$this->checkConnexion())
			return false;		
		try
		{
			$this->_dbh[$this->_currentDb]->rollback();
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Return all the columns of one table
	 *
	 * @access public	 
	 * @param string $table the table name
	 * @return array $columns array of PDO objects
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Sql::getInstance()->showColumns("table"));
	 * // will produce :
	 * array(
  	 *		0 => object(stdClass){
     *								"Field"			=> "table_id",
     *								"Type"			=> "bigint(20)",
     *								"Collation"		=> NULL,
     *								"Null"			=> "NO",
     *								"Key"			=> "PRI",
     *								"Default"		=> NULL,
     *								"Extra"			=> "auto_increment",
     *								"Privileges"	=> "select,insert,update,references",
     *								"Comment"		=> "Id"
     * 							},
     * 		1 => ...
  	 * )
	 */
	public function showColumns($table) 
	{
		if (!$this->checkConnexion())
			return false;		
		try
		{	
			$this->_generic->_time_checkpoint = microtime(true);
			$sql = "SHOW "."\n".
					"    FULL COLUMNS "."\n".
					"FROM "."\n".
					"    `".$table."` ".
					"FROM "."\n".
					"    `".$this->_generic->getDbConfig("base",$this->_currentDb)."` ";
			$statement = $this->_dbh[$this->_currentDb]->query($sql);		
			$cols = array();
			while ($result = $statement->fetchObject()) 		
				$cols[] = $result;
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query SHOW_FULL_COLUMNS","Query: "."SHOW FULL COLUMNS FROM `".$table."` FROM `".$this->_generic->getDbConfig("base",$this->_currentDb)."`","MySQL Query");
			return $cols;
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Get all the columns name of one table	 
	 *
	 * @access public
	 * @deprecated since 1.0.8
	 * @param string $table table name
	 * @return array $columns array of table columns
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Sql::getInstance()->getColumnsName("user"));
	 * // will produce
	 * array("user_id", "user_email", "user_pwd")
	 */
	public function getColumnsName($table) 
	{
		$columns = $this->showColumns($table);
		if ($columns == false)
			return false;
		else
		{
			$cols = array();		
			foreach ($columns as $col)
				array_push($cols, $col->Field);		
			return $cols;
		}
	}

	/**
	 * Insert blank row in the multilanguage case	 
	 *
	 * @access public
	 * @deprecated since 1.0.6
	 * @param string $table table name
	 * @param int $pkMultiLang one of the PKs described the entity in the case of multilanguage content (empty by default)
	 * @param string $modelLang the current language of the model
	 * @param bool $respectDefaultValue true => respect defaults values, false => erase defaults values by ''
	 * @param array $exclu fields to exclude
	 * @since 1.0
	 */
	public function insertMultiLanguageRow($table,$pkMultiLang,$modelLang,$respectDefaultValue=true,$exclu=array())
	{
		$columns = $this->showColumns($table);
		if ($columns == false)
			return false;
		else
		{
			$sqlColumn = "";
			$sqlValues = "";			
			for($i=0;$i<count($columns);$i++) {
				if (!in_array($columns[$i]->Field, $exclu)) 
				{					
					if (strtolower($columns[$i]->Key) == "pri" && $columns[$i]->Field != "pk_lang")
					{
						$sqlColumn .= "`".$columns[$i]->Field."`,";
						$sqlValues .= $pkMultiLang.",";	
					}
					else if (strtolower($columns[$i]->Key) == "pri" && $columns[$i]->Field == "pk_lang")
					{
						$sqlColumn .= "`".$columns[$i]->Field."`,";
						$sqlValues .= "'".$modelLang."',";	
					}									
					else if ($columns[$i]->Default == "" || !$respectDefaultValue)
					{
						if (stristr($columns[$i]->Type, "char") !== false or stristr($columns[$i]->Type, "text") !== false) 
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'',";
						}
						elseif (stristr($columns[$i]->Type, "int") !== false)
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'0',";	
						}
						elseif (strtolower($columns[$i]->Type) == "date")
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'0000-00-00',";	
						}
						elseif (strtolower($columns[$i]->Type) == "datetime")
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'0000-00-00 00:00:00',";	
						}
					}
				}
			}
			$sqlColumn = substr($sqlColumn, 0, (strlen($sqlColumn)-1));
			$sqlValues = substr($sqlValues, 0, (strlen($sqlValues)-1));
			$sql = "INSERT INTO `".$table."` (".$sqlColumn.") VALUES (".$sqlValues.")";			
			try
			{
				return $this->insert($sql);
			}
			catch (Exception $e)
			{			
				SLS_Tracing::addTrace($e,true);
				return false;
			}
		}		
	}
	
	/**
	 * Insert blank row in auto increment case
	 * 
	 * @access public
	 * @deprecated since 1.0.6
	 * @param string $table table name
	 * @param bool $respectDefaultValue true => respect defaults values, false => erase defaults values by ''
	 * @param array $exclu fields to exclude
	 * @return int $idInserted the id generated
	 * @since 1.0
	 */
	public function insertBlankRow($table,$respectDefaultValue=true,$exclu=array()) 
	{
		$columns = $this->showColumns($table);
		if ($columns == false)
			return false;
		else
		{
			$sqlColumn = "";
			$sqlValues = "";
			for($i=1;$i<count($columns);$i++) {
				if (!in_array($columns[$i]->Field, $exclu)) 
				{		
					if ($columns[$i]->Default == "" || !$respectDefaultValue)
					{
						if (stristr($columns[$i]->Type, "char") !== false or stristr($columns[$i]->Type, "text") !== false) 
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'',";	
						}
						elseif (stristr($columns[$i]->Type, "int") !== false)
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'0',";	
						}
						elseif (strtolower($columns[$i]->Type) == "date")
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'0000-00-00',";	
						}
						elseif (strtolower($columns[$i]->Type) == "datetime")
						{
							$sqlColumn .= "`".$columns[$i]->Field."`,";
							$sqlValues .= "'0000-00-00 00:00:00',";	
						}
					}
				}
			}
			$sqlColumn = substr($sqlColumn, 0, (strlen($sqlColumn)-1));
			$sqlValues = substr($sqlValues, 0, (strlen($sqlValues)-1));
			$sql = "INSERT INTO `".$table."` (".$sqlColumn.") VALUES (".$sqlValues.")";		
			try
			{
				return $this->insert($sql);
			}
			catch (Exception $e)
			{			
				SLS_Tracing::addTrace($e,true);
				return false;
			}
		}
	}
	
	/**
	 * Escape dangerous caracters into the sql query	 	 
	 * 
	 * @access public
	 * @param string $string the value to escape
	 * @return string $valueSecure the value escaped
	 * @since 1.0
	 */
	public function quote($string)
	{
		if (!$this->checkConnexion())
			return false;		
		try
		{
			return $this->_dbh[$this->_currentDb]->quote($string);
		}
		catch (Exception $e)
		{			
			SLS_Tracing::addTrace($e,true);
			return false;
		}
	}
	
	/**
	 * Update one table	 
	 *
	 * @access public
	 * @param string $table table name
	 * @param int $id the pk
	 * @param array $array the values
	 * @return bool $updated true if updated, else false
	 * @since 1.0
	 */
	public function updateTable($table, $id, $array) 
	{
		if ($this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"user") || $this->_generic->useModel(SLS_String::tableToClass($table),$this->_db->getCurrentDb(),"sls"))
		{
			$className = ucfirst(strtolower($this->_db->getCurrentDb()))."_".SLS_String::tableToClass($table);
			$object = new $className();
			$cols = array();
			foreach($object->getParams() as $key => $value)
				array_push($cols,$key);
		}
		else
			return false;
		if ($object->getModel($id) === false)
			return false;
		if (!is_array($array) || count($array) == 0)
			return false;
				
		$insertSql = '';
		foreach ($array as $key => $value) 		
			$insertSql .= ' `'.$key.'` = '.$this->quote($value).',';		
		if (!empty($insertSql)) 
		{	
			$insertSql = substr($insertSql, 0, (strlen($insertSql)-1));
			$sql = "UPDATE "."\n".
				   "    `".$table."` "."\n".
				   "SET "."\n".
				   "    ".$insertSql."\n".
				   "WHERE "."\n".
				   "    `".$cols[0]."` = ".$this->quote($id)."\n".
				   "LIMIT "."\n".
				   "    0, 1";
			try
			{
				$this->update($sql);
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
	 * Return status & informations regarding one or more table
	 *
	 * @access public
	 * @param string $table the table to list
	 * @return array $infos array of table(s) informations
	 * @since 1.0
	 */
	public function showTables($table=false) 
	{	
		if (!$this->checkConnexion())
			return false;	
		
		$return = array();
		$currentDbHost = $this->_generic->getDbConfig("base",$this->_currentDb);
		$session = $this->_generic->getObjectSession();
		$dbInfos = $session->getParam("sls_db_infos");
		
		if (empty($dbInfos))
			$dbInfos = array();
		if (!empty($dbInfos) && is_array($dbInfos) && array_key_exists($this->_currentDb,$dbInfos))
		{
			if ($table === false)
				$return = $dbInfos[$this->_currentDb];
			else
			{
				$tables = $dbInfos[$this->_currentDb];
				for($i=0 ; $i<$count=count($tables) ; $i++)
				{
					if ($tables[$i]->Name == $table) 
					{
						$return = $tables[$i];
						break;
					}
				}
			}
		}
		else	
		{
			$this->_generic->_time_checkpoint = microtime(true);
			
			$sql =  "SHOW "."\n".
					"    TABLE STATUS "."\n".
					"FROM "."\n".
					"    `".$currentDbHost."` ";
			
			try
			{
				$tables = $this->select($sql);
				$dbInfos[$this->_currentDb] = $tables;
				$session->setParam("sls_db_infos",$dbInfos);
			}
			catch (Exception $e)
			{
				SLS_Tracing::addTrace($e,true);
				return false;
			}
			
			for($i=0 ; $i<$count=count($tables) ; $i++)
			{
				if ($table === false) 			
					$return[] = $tables[$i];			
				else  
				{
					if ($tables[$i]->Name == $table) 
					{
						$return = $tables[$i];
						break;
					}
				}				
			}
			
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"MySQL Query SHOW_TABLE_STATUS","Query: ".$sql,"MySQL Query");
		}
		
		return $return;
	}
	
	/**
	 * Get all tables
	 * 
	 * @access public
	 * @return array $tables all tables name
	 * @since 1.0.9
	 */
	public function getTables()
	{
		$tables = array();
		$results = $this->showTables();
		for($i=0 ; $i<$count=count($results) ; $i++)
			$tables[] = $results[$i]->Name;
			
		return $tables;
	}
	
	/**
	 * Check if a table exists
	 *
	 * @access public
	 * @param string $table the table to check
	 * @return bool $exists true if ok, else false
	 * @since 1.0
	 */
	public function tableExists($table) 
	{
		if (!$this->checkConnexion())
			return false;
			
		$result = $this->showTables($table);
		if (is_array($result) && count($result) == 0) 		
			return false;		
		return true;
	}
	
	/**
	 * Check if current connexion or default connexion is ok
	 * 
	 * @access public
	 * @return bool true if ok, else false
	 * @since 1.0.6
	 */
	public function checkConnexion()
	{
		return (empty($this->_dbh) && !$this->connectToDefaultDb()) ? false : true;
	}
	
	/**
	 * Validate an order by	 
	 *
	 * @access public
	 * @param string $asc the order
	 * @return bool $valid true if of, else false
	 * @since 1.0
	 */
	public function validateAsc($asc) 
	{
		if (strtoupper($asc) != "ASC" && strtoupper($asc) != "DESC")
			return false;
		else 
			return strtoupper($asc);
	}
	
	/**
	 * Force connexion on default database
	 *
	 * @access public
	 * @since 1.0
	 */
	public function connectToDefaultDb()
	{
		$result = array_shift($this->_generic->getDbXML()->getTagsAttribute("//dbs/db[@isDefault='true']","alias"));		
		return $this->changeDb($result["attribute"]);
	}
}
?>