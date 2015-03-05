<?php
class SLS_BoAddDataBase extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$errors = array();
		$mysqlCharsets = array("armscii8","ascii","big5","binary","cp1250","cp1251","cp1256","cp1257","cp850","cp852","cp866","cp932","dec8","eucjpms","euckr","gb2312","gbk","geostd8","greek","hebrew","hp8","keybcs2","koi8u","koi8r","latin1","latin2","latin5","latin7","macce","macroman","sjis","swe7","tis620","ucs2","ujis","utf8");
		
		if ($this->_http->getParam("reload") == "true")
		{
			$dbXML = $this->_generic->getDbXML();
			
			$charset= SLS_String::trimSlashesFromString($this->_http->getParam("charset"));
			$alias 	= strtolower(SLS_String::sanitize(SLS_String::trimSlashesFromString($this->_http->getParam("alias")),""));
			$host 	= SLS_String::trimSlashesFromString($this->_http->getParam("host"));
			$db 	= SLS_String::trimSlashesFromString($this->_http->getParam("db"));
			$user 	= SLS_String::trimSlashesFromString($this->_http->getParam("user"));
			$pass 	= SLS_String::trimSlashesFromString($this->_http->getParam("pass"));
			$no_pas = SLS_String::trimSlashesFromString($this->_http->getParam("no_pass"));
			
			$result = $dbXML->getTagsAttribute("//dbs/db","alias");
			$dbs = array();
			for($i=0 ; $i<$count=count($result) ; $i++)
				array_push($dbs,$result[$i]["attribute"]);
			
			if (empty($charset) || !in_array($charset,$mysqlCharsets))
				array_push($errors, "You have to fill the charset of your database");
			if (empty($alias))
				array_push($errors,"You have to fill the alias of your database");
			if (in_array($alias,$dbs))
				array_push($errors,"This alias is already used by another database");
			if (empty($host))
				array_push($errors,"You have to fill the host of your database");
			if (empty($db))
				array_push($errors,"You have to fill the name of your database");
			if (empty($user))
				array_push($errors,"You have to fill the username of your database");
			if (empty($no_pas) && empty($pass))
				array_push($errors,"You have to fill the password of your database or to check the checkbox");
				
			if (empty($errors) && $this->_http->getParam("ping") != "true")
			{
				$host = SLS_Security::getInstance()->encrypt($host,$this->_generic->getSiteConfig("privateKey"));
				$db   = SLS_Security::getInstance()->encrypt($db,$this->_generic->getSiteConfig("privateKey"));
				$user = SLS_Security::getInstance()->encrypt($user,$this->_generic->getSiteConfig("privateKey"));
				$pass = (empty($no_pas)) ? SLS_Security::getInstance()->encrypt($pass,$this->_generic->getSiteConfig("privateKey")) : "";
				
				$str_xml = '<db alias="'.$alias.'" isDefault="'.((empty($dbs)) ? 'true' : 'false').'" isSecure="true" js="false" charset="'.$charset.'">'.
								'<host><![CDATA['.$host.']]></host>'.
								'<base><![CDATA['.$db.']]></base>'.
								'<user><![CDATA['.$user.']]></user>'.
								'<pass><![CDATA['.$pass.']]></pass>'.
							'</db>';
				$dbXML->appendXMLNode("//dbs",$str_xml);
				$dbXML->saveXML($this->_generic->getPathConfig("configSecure")."db.xml");
				$controllers = $this->_generic->getTranslatedController("SLS_Bo","DataBaseSettings");
				$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"].".sls");
			}			
			$xml->startTag("db");
			$xml->addFullTag("alias",$alias,true);
			$xml->addFullTag("charset",$charset,true);
			$xml->addFullTag("host",$host,true);
			$xml->addFullTag("db",$db,true);
			$xml->addFullTag("user",$user,true);
			$xml->endTag("db");			
		}
		$xml->startTag("charsets");
		foreach($mysqlCharsets as $key => $value)
			$xml->addFullTag("charset",$value,true);
		$xml->endTag("charsets");
		// Ping
		if ($this->_http->getParam("ping") == "true")
		{
			$sql = SLS_Sql::getInstance();
			$verdict = $sql->pingConnection($host,$db,$user,(empty($no_pas) ? $pass : ""));			
			$xml->addFullTag("ping",($verdict===true) ? "true" : $verdict,true);
		}
		if (!empty($errors) && $this->_http->getParam("ping") != "true")
		{
			$xml->startTag("errors");
			foreach($errors as $error)
				$xml->addFullTag("error",$error,true);
			$xml->endTag("errors");
		}		
		$this->saveXML($xml);
	}
}
?>