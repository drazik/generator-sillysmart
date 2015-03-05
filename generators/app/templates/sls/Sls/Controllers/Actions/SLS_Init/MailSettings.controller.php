<?php
class SLS_InitMailSettings extends SLS_InitControllerProtected 
{		
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$this->secureURL();
		$this->_generic->registerLink('MailSettings', 'SLS_Init', 'MailSettings');
		$step = 0;
		$xml = $this->getXML();
		$errors = array();
		if ($this->_http->getParam("mails_reload") == "1")
		{
			$useSmtp = $this->_http->getParam("mails_useSmtp");
			if (empty($useSmtp))
				array_push($errors, "Will you need SMTP connection?");
			else
			{
				if ($this->_http->getParam("mails_useSmtp") == "false")
				{
					$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"Final",1=>"Congratulations"));
					return $this->_generic->dispatch("SLS_Init", "Final");	
				}
				else
				{
					$step = 1;
					$domainName = $this->_generic->getSiteConfig("domainName");
					$port = 25;
					$xmlToolBox = $this->getXML();
					$xmlToolBox->addFullTag("defaultDomain",$domainName,true);
					$xmlToolBox->addFullTag("port",$port,true);
					$xmlToolBox->addFullTag("header",'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="FFFFFF" color="000000">',true);
					$xmlToolBox->addFullTag("footer",'</body></html>',true);
					$this->saveXML($xmlToolBox);
				}
			}
		}
		elseif ($this->_http->getParam("mails_reload") == "2")
		{			
			$host 				= SLS_String::trimSlashesFromString($this->_http->getParam("host"));
			$port 				= ($this->_http->getParam("port")=="") ? 25 : SLS_String::trimSlashesFromString($this->_http->getParam("port"));
			$username 			= SLS_String::trimSlashesFromString($this->_http->getParam("username"));
			$password			= SLS_String::trimSlashesFromString($this->_http->getParam("password"));
			$password2			= SLS_String::trimSlashesFromString($this->_http->getParam("password2"));
			$defaultDomain		= ($this->_http->getParam("defaultDomain") == "") ? $this->_generic->getSiteConfig("domainName") : SLS_String::trimSlashesFromString($this->_http->getParam("defaultDomain"));
			$defaultNameSender	= SLS_String::trimSlashesFromString($this->_http->getParam("defaultNameSender"));
			$defaultSender		= SLS_String::trimSlashesFromString($this->_http->getParam("defaultSender"));
			$defaultNameReply	= SLS_String::trimSlashesFromString($this->_http->getParam("defaultNameReply"));
			$defaultReply		= SLS_String::trimSlashesFromString($this->_http->getParam("defaultReply"));
			$defaultNameReturn	= SLS_String::trimSlashesFromString($this->_http->getParam("defaultNameReturn"));
			$defaultReturn		= SLS_String::trimSlashesFromString($this->_http->getParam("defaultReturn"));
			$header				= SLS_String::trimSlashesFromString($this->_http->getParam("header"));
			$footer				= SLS_String::trimSlashesFromString($this->_http->getParam("footer"));
			
			if (empty($host))
				array_push($errors, "You have to fill the SMTP Host");
			if (empty($port))
				array_push($errors, "You have to fill the SMTP Port");
			if (empty($username))
				array_push($errors, "You have to fill the SMTP Username");
			if (empty($password))
				array_push($errors, "You have to fill the SMTP Password");
			else if ($password != $password2)
				array_push($errors, "Both passwords don't match");
			if (empty($defaultDomain))
				array_push($errors, "You have to fill the SMTP Domain");			
			if (empty($defaultNameSender))
				array_push($errors, "You have to fill the `From`' name");
			if (empty($defaultSender))
				array_push($errors, "You have to fill the `From`' alias");
			if (empty($defaultNameReply))
				array_push($errors, "You have to fill the `Reply To`' name");
			if (empty($defaultReply))
				array_push($errors, "You have to fill the `Reply To`' alias");
			if (empty($defaultNameReturn))
				array_push($errors, "You have to fill the `Return-Path`' name");
			if (empty($defaultReturn))
				array_push($errors, "You have to fill the `Return-Path`' alias");
			
			if (empty($errors) && $this->_http->getParam("ping") != "true")
			{
				$dbXml = $this->_generic->getMailXML();
				$dbXml->setTag("host", SLS_Security::getInstance()->encrypt($this->_http->getParam("host"), $this->_generic->getSiteConfig("privateKey")));
				$dbXml->setTag("port", SLS_Security::getInstance()->encrypt($this->_http->getParam("port"), $this->_generic->getSiteConfig("privateKey")));
				$dbXml->setTag("username", SLS_Security::getInstance()->encrypt($this->_http->getParam("username"), $this->_generic->getSiteConfig("privateKey")));
				$dbXml->setTag("password", SLS_Security::getInstance()->encrypt($this->_http->getParam("password"), $this->_generic->getSiteConfig("privateKey")));
				$dbXml->setTag("defaultDomain",$defaultDomain);
				$dbXml->setTag("defaultSender",$defaultSender);
				$dbXml->setTag("defaultNameSender",$defaultNameSender);
				$dbXml->setTag("defaultReply",$defaultReply);
				$dbXml->setTag("defaultNameReply",$defaultNameReply);
				$dbXml->setTag("defaultReturn",$defaultReturn);
				$dbXml->setTag("defaultNameReturn",$defaultNameReturn);
				// Default Template
				$mailTpl = '<item id="default" isSecure="false" js="false" default="true">';
				$mailTpl .= '<header isSecure="false" js="false"><![CDATA['.$header.']]></header>';
				$mailTpl .= '<footer isSecure="false" js="false"><![CDATA['.$footer.']]></footer>';
				$mailTpl .= '</item>';
				$dbXml->appendXMLNode("//mails/templates",$mailTpl);
				file_put_contents($this->_generic->getPathConfig("configSecure")."mail.xml", $dbXml->getXML());
				
				$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"Final",1=>"Congratulations"));
				return $this->_generic->dispatch("SLS_Init", "Final");
			}
			else
			{
				$xmlToolBox = $this->getXML();
				$xmlToolBox->addFullTag("host",$host,true);
				$xmlToolBox->addFullTag("port",$port,true);
				$xmlToolBox->addFullTag("username",$username,true);
				$xmlToolBox->addFullTag("password",$password,true);
				$xmlToolBox->addFullTag("defaultDomain",$defaultDomain,true);
				$xmlToolBox->addFullTag("defaultSender",$defaultSender,true);
				$xmlToolBox->addFullTag("defaultNameSender",$defaultNameSender,true);
				$xmlToolBox->addFullTag("defaultReply",$defaultReply,true);
				$xmlToolBox->addFullTag("defaultNameReply",$defaultNameReply,true);
				$xmlToolBox->addFullTag("defaultReturn",$defaultReturn,true);
				$xmlToolBox->addFullTag("defaultNameReturn",$defaultNameReturn,true);
				$xmlToolBox->addFullTag("header",$header,true);
				$xmlToolBox->addFullTag("footer",$footer,true);
				$this->saveXML($xmlToolBox);
			}
			$step = 1;
		}
		if (!empty($errors) && $this->_http->getParam("ping") != "true")
		{
			$xml->startTag("errors");
			foreach($errors as $error)
				$xml->addFullTag("error", $error, true);
			$xml->endTag("errors");
		}
		if ($this->_http->getParam("ping") == "true")
		{
			$smtp = new SLS_Email("");
			$verdict = $smtp->pingConnection($host,$port,$username,$password);
			$xml->addFullTag("ping",($verdict===true) ? "true" : $verdict,true);
		}
					
		
		$xml->addFullTag("step", $step, true);
		$this->saveXML($xml);
	}
	
}
?>