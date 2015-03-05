<?php
/**
* Class ReportingBo into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBo extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$db = SLS_Sql::getInstance();
				
		// Check if we need to create sls_graph_* tables
		if (!$db->tableExists("sls_graph") || !file_exists($this->_generic->getPathConfig("coreSlsModels").ucfirst($this->defaultDb)."Sls_graph.model.php"))
		{
			// Create tables
			$queries = array($this->defaultDb.".sls_graph" 				=> "CREATE TABLE IF NOT EXISTS `sls_graph` (`sls_graph_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_title` varchar(255) NOT NULL COMMENT 'Title', `sls_graph_type` enum('pie','bar','pivot','list') NOT NULL COMMENT 'Type', `sls_graph_visible` enum('yes','no') NOT NULL DEFAULT 'yes' COMMENT 'Visible?', `sls_graph_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', PRIMARY KEY (`sls_graph_id`), KEY `sls_graph_visible` (`sls_graph_visible`), KEY `sls_graph_query_id` (`sls_graph_query_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query" 		=> "CREATE TABLE IF NOT EXISTS `sls_graph_query` (`sls_graph_query_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_db_alias` varchar(255) NOT NULL COMMENT 'Db alias', `sls_graph_query_table` varchar(255) NOT NULL COMMENT 'Table name', `sls_graph_query_table_alias` varchar(255) NOT NULL COMMENT 'Table alias', `sls_graph_query_date_add` datetime NOT NULL COMMENT 'Date add', PRIMARY KEY (`sls_graph_query_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Query' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query_column" => "CREATE TABLE IF NOT EXISTS `sls_graph_query_column` (`sls_graph_query_column_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_column_table` varchar(255) NOT NULL COMMENT 'Table name', `sls_graph_query_column_table_alias` varchar(255) NOT NULL COMMENT 'Table alias', `sls_graph_query_column_name` varchar(255) DEFAULT NULL COMMENT 'Column name', `sls_graph_query_column_alias` varchar(255) DEFAULT NULL COMMENT 'Column alias', `sls_graph_query_column_aggregation` enum('sum','avg','count') DEFAULT NULL COMMENT 'Aggregation function', `sls_graph_query_column_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', PRIMARY KEY (`sls_graph_query_column_id`), KEY `sls_graph_query_id` (`sls_graph_query_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Column' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query_group" 	=> "CREATE TABLE IF NOT EXISTS `sls_graph_query_group` (`sls_graph_query_group_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_group_table` varchar(255) NOT NULL COMMENT 'Table name', `sls_graph_query_group_table_alias` varchar(255) NOT NULL COMMENT 'Table alias', `sls_graph_query_group_column` varchar(255) NOT NULL COMMENT 'Column', `sls_graph_query_group_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', PRIMARY KEY (`sls_graph_query_group_id`), KEY `sls_graph_query_id` (`sls_graph_query_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Group' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query_join" 	=> "CREATE TABLE IF NOT EXISTS `sls_graph_query_join` (`sls_graph_query_join_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_join_table_source` varchar(255) NOT NULL COMMENT 'Table name source', `sls_graph_query_join_table_alias_source` varchar(255) NOT NULL COMMENT 'Table alias source', `sls_graph_query_join_table_target` varchar(255) NOT NULL COMMENT 'Table name target', `sls_graph_query_join_table_alias_target` varchar(255) NOT NULL COMMENT 'Table alias target', `sls_graph_query_join_mode` enum('inner','left','right') NOT NULL COMMENT 'Join mode', `sls_graph_query_join_column_source` varchar(255) NOT NULL COMMENT 'Column PK', `sls_graph_query_join_column_target` varchar(255) NOT NULL COMMENT 'Column FK', `sls_graph_query_join_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', PRIMARY KEY (`sls_graph_query_join_id`), KEY `sls_graph_query_id` (`sls_graph_query_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Join' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query_limit" 	=> "CREATE TABLE IF NOT EXISTS `sls_graph_query_limit` (`sls_graph_query_limit_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_limit_start` int(11) NOT NULL COMMENT 'Start', `sls_graph_query_limit_length` int(11) NOT NULL COMMENT 'Length', `sls_graph_query_limit_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', PRIMARY KEY (`sls_graph_query_limit_id`), KEY `sls_graph_query_id` (`sls_graph_query_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Limit' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query_order" 	=> "CREATE TABLE IF NOT EXISTS `sls_graph_query_order` (`sls_graph_query_order_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_order_table` varchar(255) NOT NULL COMMENT 'Table name', `sls_graph_query_order_table_alias` varchar(255) NOT NULL COMMENT 'Table alias', `sls_graph_query_order_column` varchar(255) NOT NULL COMMENT 'Column', `sls_graph_query_order_way` enum('asc','desc') NOT NULL COMMENT 'Order', `sls_graph_query_order_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', PRIMARY KEY (`sls_graph_query_order_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Order' AUTO_INCREMENT=1;",
							 $this->defaultDb.".sls_graph_query_where" 	=> "CREATE TABLE IF NOT EXISTS `sls_graph_query_where` (`sls_graph_query_where_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Id', `sls_graph_query_where_type` enum('group','clause') NOT NULL COMMENT 'Type', `sls_graph_query_where_condition` enum('and','or') DEFAULT NULL COMMENT 'Condition', `sls_graph_query_where_table` varchar(255) DEFAULT NULL COMMENT 'Table name', `sls_graph_query_where_table_alias` varchar(255) DEFAULT NULL COMMENT 'Table alias', `sls_graph_query_where_column` varchar(255) DEFAULT NULL COMMENT 'Column', `sls_graph_query_where_operator` enum('like','notlike','startwith','endwith','equal','notequal','in','notin','lt','lte','gt','gte','null','notnull') DEFAULT NULL COMMENT 'Operator', `sls_graph_query_where_value` varchar(255) DEFAULT NULL COMMENT 'Value', `sls_graph_query_where_date_add` datetime NOT NULL COMMENT 'Date add', `sls_graph_query_id` bigint(20) NOT NULL COMMENT 'Query', `parent_sls_graph_query_where_id` bigint(20) DEFAULT '0' COMMENT 'Where Parent', PRIMARY KEY (`sls_graph_query_where_id`), KEY `sls_graph_query_id` (`sls_graph_query_id`), KEY `parent_sls_graph_query_where_id` (`parent_sls_graph_query_where_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reporting - Graph - Where' AUTO_INCREMENT=1;");
			foreach($queries as $table => $query)
				$db->exec($query);
			
			// Create models
			$url = $this->_generic->getFullPath("SLS_Bo",
											    "GenerateModels",
											    array("reload" => "true",
											   		  "token"  => sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))),
											    false);
			$tables = array_keys($queries);
			foreach($tables as $table)
				$url .= "/tables[]/".$table;
			$url .= ".".$this->_generic->getSiteConfig("defaultExtension");
			file_get_contents($url);
			
			// Move to sls side
			foreach(array_keys($queries) as $model)
			{
				$modelDb = SLS_String::substrBeforeFirstDelimiter($model,".");
				$modelTable = SLS_String::substrAfterFirstDelimiter($model,".");
				$modelClass = ucfirst($modelDb).".".SLS_String::tableToClass($modelTable);
				
				// Move object
				if (file_exists($this->_generic->getPathConfig("models").$modelClass.".model.php"))
					rename($this->_generic->getPathConfig("models").$modelClass.".model.php",$this->_generic->getPathConfig("coreSlsModels").$modelClass.".model.php");
				// Move sql
				if (file_exists($this->_generic->getPathConfig("modelsSql").$modelClass.".sql.php"))
					rename($this->_generic->getPathConfig("modelsSql").$modelClass.".sql.php",$this->_generic->getPathConfig("coreSlsModelsSql").$modelClass.".sql.php");
			}
		}
		
		// Objects
		$this->_generic->useModel('Sls_graph',$this->defaultDb,'sls');
		$className = ucfirst($this->defaultDb)."_Sls_graph";
		$graph = new $className();
		
		$graphs = $graph->searchModels("sls_graph",array(),array(),array(),array("sls_graph_date_add" => "desc"));
		$xml = $graph->pdoToXML($xml,$graphs);
		
		// Actions
		$this->_generic->registerLink('Add', 'SLS_Bo', 'ReportingBoAdd');
		$xml->addFullTag("url_view",$this->_generic->getFullPath("SLS_Bo","ReportingBoView",array(),false));
		$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","ReportingBoDelete",array(),false));
		$xml->addFullTag("url_edit",$this->_generic->getFullPath("SLS_Bo","ReportingBoEdit",array(),false));
		$xml->addFullTag("url_status",$this->_generic->getFullPath("SLS_Bo","ReportingBoStatus",array(),false));
		
		$this->saveXML($xml);
	}
}
?>