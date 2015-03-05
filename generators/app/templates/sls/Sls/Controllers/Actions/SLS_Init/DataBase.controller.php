<?php
class SLS_InitDataBase extends SLS_InitControllerProtected 
{		
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$this->secureURL();
		$this->_generic->registerLink('DataBase', 'SLS_Init', 'DataBase');
		$step = 0;
		$xml = $this->getXML();
		$errors = array();
		$mysqlCharsets = array("armscii8","ascii","big5","binary","cp1250","cp1251","cp1256","cp1257","cp850","cp852","cp866","cp932","dec8","eucjpms","euckr","gb2312","gbk","geostd8","greek","hebrew","hp8","keybcs2","koi8u","koi8r","latin1","latin2","latin5","latin7","macce","macroman","sjis","swe7","tis620","ucs2","ujis","utf8");
		
		if ($this->_http->getParam("database_reload") == "1")
		{
			$useSql = $this->_http->getParam("database_useSql");
			if (empty($useSql))
				array_push($errors, "Will you need MySQL connection?");
			else
			{
				if ($this->_http->getParam("database_useSql") == "false")
				{
					$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"MailSettings",1=>"MailSettings"));
					return $this->_generic->dispatch("SLS_Init", "MailSettings");	
				}
				else
				{
					$step = 1;
					$nbDbs = $this->_http->getParam("nb_databases");
					if (empty($nbDbs) || !is_numeric($nbDbs) || $nbDbs <= 0)
						$nbDbs = 1;
					$xml->startTag("nb_databases");
					for($i=0 ; $i<$nbDbs ; $i++)
						$xml->addFullTag("nb_database","",true);
					$xml->endTag("nb_databases");
				}
			}
		}
		elseif ($this->_http->getParam("database_reload") == "2")
		{
			$nb_databases = SLS_String::trimSlashesFromString($this->_http->getParam("nb_databases"));
			if (empty($nb_databases) || !is_numeric($nb_databases) || $nb_databases <= 0)
				$nb_databases = 1;
			$xml->startTag("nb_databases");
			for($i=0 ; $i<$nb_databases ; $i++)
				$xml->addFullTag("nb_database","",true);
			$xml->endTag("nb_databases");
			$nicks_used = array();
			
			for($i=1 ; $i<=$nb_databases ; $i++)
			{
				$nick_{$i} 		= strtolower(SLS_String::tableToClass(SLS_String::trimSlashesFromString($this->_http->getParam("database_alias_".$i))));
				$charset_{$i} 	= SLS_String::trimSlashesFromString($this->_http->getParam("database_charset_".$i));
				$host_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("database_host_".$i));			
				$name_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("database_name_".$i));
				$user_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("database_user_".$i));
				$pass_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("database_pass_".$i));
				$no_p_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("database_no_pass_".$i));
				
				if (empty($charset_{$i}) || !in_array($charset_{$i},$mysqlCharsets))
					array_push($errors, "You have to fill the charset for the database n°".$i);
				if (empty($nick_{$i}))
					array_push($errors, "You have to fill the database alias for the database n°".$i);
				else if (in_array($nick_{$i},$nicks_used))
					array_push($errors, "The database alias must be unique");
				else
					array_push($nicks_used,$nick_{$i});
					
				if (empty($host_{$i}))
					array_push($errors, "You have to fill the database host for the database n°".$i);
				if (empty($name_{$i}))
					array_push($errors, "You have to fill the database name for the database n°".$i);
				if (empty($user_{$i}))
					array_push($errors, "You have to fill the database username for the database n°".$i);
				if (empty($no_p_{$i}) && empty($pass_{$i}))
					array_push($errors, "You have to fill the database password or to check 'No password' for the database n°".$i);
			}
			
			// Ping
			if ($this->_http->getParam("ping") == "true")
			{
				$sql = SLS_Sql::getInstance();
				$errorsP = array();
				for($i=1 ; $i<=$nb_databases ; $i++)
				{					
					$verdict = $sql->pingConnection($host_{$i},$name_{$i},$user_{$i},(empty($no_p_{$i}) ? $pass_{$i} : ""));					
					if ($verdict === true)
						array_push($errorsP,'<li style="color:green;"><u>Database n°'.$i.':</u><br />Connection successfull</li>');
					if ($verdict !== true)
						array_push($errorsP,'<li style="color:red;"><u>Database n°'.$i.':</u><br />Connection failed with message `'.$verdict.'`</li>');
				}				
				$xml->addFullTag("ping",(empty($errorsP)) ? "true" : "false",true);
				$xml->startTag("ping_msgs");
				foreach($errorsP as $errorP)
					$xml->addFullTag("ping_msg",$errorP,true);
				$xml->endTag("ping_msgs");
			}
			
			// If it have errors
			//if (!empty($errors))
			//{
				$xml->startTag("dbs");
				for($i=1 ; $i<=$nb_databases ; $i++)
				{
					$xml->startTag("db_".$i);
					$xml->addFullTag("alias",$nick_{$i},true);
					$xml->addFullTag("charset",$charset_{$i},true);
					$xml->addFullTag("host",$host_{$i},true);
					$xml->addFullTag("name",$name_{$i},true);
					$xml->addFullTag("user",$user_{$i},true);
					$xml->addFullTag("pass",$pass_{$i},true);
					$xml->addFullTag("no_pass",(empty($no_p_{$i}))?"false":"true",true);
					$xml->endTag("db_".$i);
				}
				$xml->endTag("dbs");
			//}
			// Good, we can save it!
			if (empty($errors)  && $this->_http->getParam("ping") != "true")
			{
				$dbXml 		= $this->_generic->getDbXML();
				$str_xml 	= "";
				for($i=1 ; $i<=$nb_databases ; $i++)
				{
					$default = ($i==1) ? 'true' : 'false';
					
					$host_{$i} = SLS_Security::getInstance()->encrypt($host_{$i},$this->_generic->getSiteConfig("privateKey"));
					$name_{$i} = SLS_Security::getInstance()->encrypt($name_{$i},$this->_generic->getSiteConfig("privateKey"));
					$user_{$i} = SLS_Security::getInstance()->encrypt($user_{$i},$this->_generic->getSiteConfig("privateKey"));
					$pass_{$i} = (empty($no_p_{$i})) ? SLS_Security::getInstance()->encrypt($pass_{$i},$this->_generic->getSiteConfig("privateKey")) : "";
					
					$str_xml .= '<db alias="'.$nick_{$i}.'" isDefault="'.$default.'" isSecure="true" js="false" charset="'.$charset_{$i}.'">'.
									'<host><![CDATA['.$host_{$i}.']]></host>'.
									'<base><![CDATA['.$name_{$i}.']]></base>'.
									'<user><![CDATA['.$user_{$i}.']]></user>'.
									'<pass><![CDATA['.$pass_{$i}.']]></pass>'.
								'</db>';
				}
				$dbXml->appendXMLNode("//dbs",$str_xml);
				$dbXml->saveXML($this->_generic->getPathConfig("configSecure")."db.xml");
				$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"MailSettings",1=>"MailSettings"));
				
				// If old sls_graph_* exists
				$sql = SLS_Sql::getInstance();
				$graphTables = array("sls_graph",
									 "sls_graph_query",
									 "sls_graph_query_column",
									 "sls_graph_query_group",
									 "sls_graph_query_join",
									 "sls_graph_query_limit",
									 "sls_graph_query_order",
									 "sls_graph_query_where");
				foreach($graphTables as $graphTable)
				{
					try 
					{
						if ($sql->tableExists($graphTable))
							$sql->exec("DROP TABLE `".$graphTable."`");
					}
					catch (Exception $e)
					{
						continue;
					}
				}
				return $this->_generic->dispatch("SLS_Init", "MailSettings");
			}			
			$step = 1;
		}
		
		if (!empty($errors) && $this->_http->getParam("database_reload") == "1" || ( !empty($errors) && $this->_http->getParam("ping") != "true"))
		{
			$xml->startTag("errors");
			foreach($errors as $error)
				$xml->addFullTag("error", $error, true);
			$xml->endTag("errors");
		}
		$xml->addFullTag("step", $step, true);
		$xml->startTag("charsets");
		foreach($mysqlCharsets as $key => $value)
			$xml->addFullTag("charset",$value,true);
		$xml->endTag("charsets");
		$this->saveXML($xml);
	}
	
}
?>