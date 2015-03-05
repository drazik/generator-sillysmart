<?php
class SLS_BoDataBaseSettings extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		
		$errors = array();
		
		// Prod Deployment
		$env = $this->_http->getParam("Env");
		if (empty($env))
			$env = "prod";
		$finalFile = ($this->_http->getParam("ProdDeployment") == "true") ? "db_".$env.".xml" : "db.xml";
		$isInBatch = ($this->_http->getParam("CompleteBatch") == "true") ? true : false;
		$xml->addFullTag("is_batch",($isInBatch) ? "true" : "false",true);
		$xml->addFullTag("is_prod",($this->_http->getParam("ProdDeployment") == "true") ? "true" : "false",true);
		
		if ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/db_".$env.".xml"))					
			$dbXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/db_".$env.".xml"));
		else		
			$dbXML = $this->_generic->getDbXML();
		
		$nbDbs = count($dbXML->getTags("//dbs/db"));
		$mysqlCharsets = array("armscii8","ascii","big5","binary","cp1250","cp1251","cp1256","cp1257","cp850","cp852","cp866","cp932","dec8","eucjpms","euckr","gb2312","gbk","geostd8","greek","hebrew","hp8","keybcs2","koi8u","koi8r","latin1","latin2","latin5","latin7","macce","macroman","sjis","swe7","tis620","ucs2","ujis","utf8");
		
		if ($this->_http->getParam("reload") == "true")
		{
			$exportConfig = $this->_http->getParam('export');
			$result = $dbXML->getTagsAttribute("//dbs/db","alias");
			for($i=0 ; $i<$count=count($result) ; $i++)
			{
				$current_alias = $result[$i]["attribute"];
				
				$charset_{$i} 	= SLS_String::trimSlashesFromString($this->_http->getParam("charset_".$current_alias));
				$host_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("host_".$current_alias));
				$base_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("base_".$current_alias));
				$user_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("user_".$current_alias));
				$pass_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("pass_".$current_alias));
				$no_p_{$i} 		= SLS_String::trimSlashesFromString($this->_http->getParam("no_pass_".$current_alias));
				
				if (empty($charset_{$i}) || !in_array($charset_{$i},$mysqlCharsets))
					array_push($errors, "You have to fill the charset for the database '".$current_alias."'");
				if (empty($host_{$i}))
					array_push($errors, "You have to fill the database host for the database '".$current_alias."'");
				if (empty($base_{$i}))
					array_push($errors, "You have to fill the database name for the database '".$current_alias."'");
				if (empty($user_{$i}))
					array_push($errors, "You have to fill the database username for the database '".$current_alias."'");
				if (empty($no_p_{$i}) && empty($pass_{$i}))
					array_push($errors, "You have to fill the database password or to check 'Don't require a password' for the database '".$current_alias."'");
			}
			
			if (empty($errors))
			{
				for($i=0 ; $i<$count=count($result) ; $i++)
				{
					$current_alias = $result[$i]["attribute"];
					
					$charset_{$i} 	= SLS_String::trimSlashesFromString($this->_http->getParam("charset_".$current_alias));
					$host_{$i} 		= SLS_Security::getInstance()->encrypt($host_{$i},$this->_generic->getSiteConfig("privateKey"));
					$base_{$i} 		= SLS_Security::getInstance()->encrypt($base_{$i},$this->_generic->getSiteConfig("privateKey"));
					$user_{$i} 		= SLS_Security::getInstance()->encrypt($user_{$i},$this->_generic->getSiteConfig("privateKey"));
					$pass_{$i} 		= (empty($no_p_{$i})) ? SLS_Security::getInstance()->encrypt($pass_{$i},$this->_generic->getSiteConfig("privateKey")) : "";
					
					$dbXML->setTagAttributes("//dbs/db[@alias='".$current_alias."']",array("charset"=>$charset_{$i}));
					$dbXML->setTag("//dbs/db[@alias='".$current_alias."']/host",$host_{$i});
					$dbXML->setTag("//dbs/db[@alias='".$current_alias."']/base",$base_{$i});
					$dbXML->setTag("//dbs/db[@alias='".$current_alias."']/user",$user_{$i});
					if (empty($no_p_{$i}))
						$dbXML->setTag("//dbs/db[@alias='".$current_alias."']/pass",$pass_{$i});
					else
						$dbXML->setTag("//dbs/db[@alias='".$current_alias."']/pass","");
					
				}
				if ($exportConfig == "on")
				{
					$date = gmdate('D, d M Y H:i:s');
					header("Content-Type: text/xml"); 
					header('Content-Disposition: attachment; filename='.$finalFile);
					header('Last-Modified: '.$date. ' GMT');
					header('Expires: ' .$date);
					// For This Fuck'in Browser
					if(preg_match('/msie|(microsoft internet explorer)/i', $_SERVER['HTTP_USER_AGENT']))
					{
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
					}
					else
						header('Pragma: no-cache');
					
					print($dbXML->getXML());
					exit; 
				}
				else
				{ 			
					$dbXML->refresh();	
					@file_put_contents($this->_generic->getPathConfig("configSecure").$finalFile, $dbXML->getXML());
					if ($isInBatch)
						$this->_generic->forward("SLS_Bo","MailSettings",array(array("key"=>"ProdDeployment","value"=>"true"),array("key"=>"CompleteBatch","value"=>"true"),array("key"=>"Env","value"=>$env)));
					else if ($this->_http->getParam("ProdDeployment") == "true")
						$this->_generic->forward("SLS_Bo","ProductionDeployment");
				}
			}
		}
		
		$this->_generic->eraseCache('Dbs');
		
		
		$xml->startTag("dbs");
		for($i=1 ; $i<=$nbDbs ; $i++)
		{
			$result 	= array_shift($dbXML->getTagsAttribute("//dbs/db[".$i."]","alias"));
			$alias 		= $result["attribute"];
			$result		= array_shift($dbXML->getTagsAttribute("//dbs/db[".$i."]","isDefault"));
			$default 	= $result["attribute"];
			$result 	= array_shift($dbXML->getTagsAttribute("//dbs/db[".$i."]","charset"));			
			$charset	= $result["attribute"];
			
			$xml->startTag("db");
			$xml->addFullTag("alias",$alias,true);
			$xml->addFullTag("default",$default,true);
			$xml->addFullTag("charset",$charset,true);
			$xml->addFullTag("host",($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/db_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($dbXML->getTags("//dbs/db[@alias='".$alias."']/host")), $this->_generic->getSiteConfig("privateKey")) : $this->_generic->getDbConfig("host",$alias),true);
			$xml->addFullTag("base",($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/db_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($dbXML->getTags("//dbs/db[@alias='".$alias."']/base")), $this->_generic->getSiteConfig("privateKey")) : $this->_generic->getDbConfig("base",$alias),true);
			$xml->addFullTag("user",($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/db_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($dbXML->getTags("//dbs/db[@alias='".$alias."']/user")), $this->_generic->getSiteConfig("privateKey")) : $this->_generic->getDbConfig("user",$alias),true);
			$xml->addFullTag("pass",($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/db_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($dbXML->getTags("//dbs/db[@alias='".$alias."']/pass")), $this->_generic->getSiteConfig("privateKey")) : $this->_generic->getDbConfig("pass",$alias),true);
			$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","DeleteDataBase",array(array("key"=>"alias","value"=>rawurlencode($alias)))),true);
			$xml->endTag("db");
		}
		$xml->endTag("dbs");
		
		if (!empty($errors))
		{
			$xml->startTag("errors");
			foreach($errors as $error)
				$xml->addFullTag("error",$error,true);
			$xml->endTag("errors");
		}
		$xml->addFullTag("url_add_database",$this->_generic->getFullPath("SLS_Bo","AddDataBase"),true);
		$xml->startTag("charsets");
		foreach($mysqlCharsets as $key => $value)
			$xml->addFullTag("charset",$value,true);
		$xml->endTag("charsets");
		$this->saveXML($xml);
	}
	
}
?>