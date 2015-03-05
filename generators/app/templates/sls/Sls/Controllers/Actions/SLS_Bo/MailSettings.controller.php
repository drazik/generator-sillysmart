<?php
class SLS_BoMailSettings extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		
		$env = $this->_http->getParam("Env");
		if (empty($env))
			$env = "prod";
		
		if ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml"))					
			$mailXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml"));
		else		
			$mailXML = $this->_generic->getMailXML();
		
		$errors = array();
		
		// Prod Deployment		
		$finalFile = ($this->_http->getParam("ProdDeployment") == "true") ? "mail_".$env.".xml" : "mail.xml";
		$isInBatch = ($this->_http->getParam("CompleteBatch") == "true") ? true : false;
		$xml->addFullTag("is_batch",($isInBatch) ? "true" : "false",true);
		$xml->addFullTag("is_prod",($this->_http->getParam("ProdDeployment") == "true") ? "true" : "false",true);
		
		// Get default values
		$defaultHost 		= $this->_generic->getMailConfig("host");
		$defaultPort		= $this->_generic->getMailConfig("port"); 
		$defaultUser 		= $this->_generic->getMailConfig("username");
		$defaultPass		= $this->_generic->getMailConfig("passsword"); 
		$defaultDomain 		= $this->_generic->getMailConfig("defaultDomain");
		$defaultReturn		= $this->_generic->getMailConfig("defaultReturn"); 
		$defaultNameReturn	= $this->_generic->getMailConfig("defaultNameReturn");
		$defaultReply		= $this->_generic->getMailConfig("defaultReply");
		$defaultNameReply	= $this->_generic->getMailConfig("defaultNameReply");
		$defaultSender		= $this->_generic->getMailConfig("defaultSender"); 
		$defaultNameSender	= $this->_generic->getMailConfig("defaultNameSender");
		$TemplatesHeader	= $mailXML->getTags("//mails/templates/item[@id='default']/header");
		$TemplatesFooter	= $mailXML->getTags("//mails/templates/item[@id='default']/footer");
		
		$reload 			= $this->_http->getParam("reload");
				
		if ($reload == "true")
		{
			// Get New Parameters
			$exportConfig	= $this->_http->getParam('export');
			
			$postHost 		= SLS_String::trimSlashesFromString($this->_http->getParam("host", "post"));
			$postPort		= SLS_String::trimSlashesFromString($this->_http->getParam("port", "post"));
			$postUser 		= SLS_String::trimSlashesFromString($this->_http->getParam("user", "post"));
			$postPass		= SLS_String::trimSlashesFromString($this->_http->getParam("pass", "post"));
			$postDomain		= SLS_String::trimSlashesFromString($this->_http->getParam("domain", "post"));
			$postReturn		= SLS_String::trimSlashesFromString($this->_http->getParam("return", "post")); 
			$postNameReturn	= SLS_String::trimSlashesFromString($this->_http->getParam("nameReturn", "post"));;
			$postReply		= SLS_String::trimSlashesFromString($this->_http->getParam("reply", "post"));
			$postNameReply	= SLS_String::trimSlashesFromString($this->_http->getParam("nameReply", "post"));
			$postSender		= SLS_String::trimSlashesFromString($this->_http->getParam("sender", "post")); 
			$postNameSender	= SLS_String::trimSlashesFromString($this->_http->getParam("nameSender", "post"));
			$postPassNeed	= SLS_String::trimSlashesFromString($this->_http->getParam("needpass", "post"));
			$postUserNeed	= SLS_String::trimSlashesFromString($this->_http->getParam("needuser", "post"));
			
			$varsPost = $this->_http->getParams("post");
			
			if ($postUserNeed != "on" && empty($postUser))
				array_push($errors, "You need to fill the username");
			if ($postPassNeed != "on" && empty($postPass))
				array_push($errors, "You need to fill the password");
			if (empty($errors) && $this->_http->getParam("ping") != "true")
			{			
				if ($defaultHost != $postHost)
					 $mailXML->setTag("//mails/host", SLS_Security::getInstance()->encrypt($postHost, $this->_generic->getSiteConfig("privateKey")), true);
				if ($defaultPort != $postPort)
					 $mailXML->setTag("//mails/port", SLS_Security::getInstance()->encrypt($postPort, $this->_generic->getSiteConfig("privateKey")), true);
				if ($defaultReturn != $postReturn)
					 $mailXML->setTag("//mails/defaultReturn", $postReturn, true);
				if ($defaultDomain != $postDomain)
					 $mailXML->setTag("//mails/defaultDomain", $postDomain, true);
				if ($defaultNameReturn != $postNameReturn)
					 $mailXML->setTag("//mails/defaultNameReturn", $postNameReturn, true);
				if ($defaultReply != $postReply)
					 $mailXML->setTag("//mails/defaultReply", $postReply, true);
				if ($defaultNameReply != $postNameReply)
					 $mailXML->setTag("//mails/defaultNameReply", $postNameReply, true);
				if ($defaultSender != $postSender)
					 $mailXML->setTag("//mails/defaultSender", $postSender, true);
				if ($defaultNameSender != $postNameSender)
					 $mailXML->setTag("//mails/defaultNameSender", $postNameSender, true);
				
				if ($postUserNeed == "on")
					$mailXML->setTag("//mails/username", "", true);
				else 
					$mailXML->setTag("//mails/username", SLS_Security::getInstance()->encrypt($postUser, $this->_generic->getSiteConfig("privateKey")), true); 
				if ($postPassNeed == "on")
					$mailXML->setTag("//mails/password", "", true);
				else 
					$mailXML->setTag("//mails/password", SLS_Security::getInstance()->encrypt($postPass, $this->_generic->getSiteConfig("privateKey")), true);
				
					
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
					
					print($mailXML->getXML());
					exit; 
				}
				else 
				{
					$mailXML->refresh();	
					@file_put_contents($this->_generic->getPathConfig("configSecure").$finalFile, $mailXML->getXML());
					if ($isInBatch)
						$this->_generic->forward("SLS_Bo","ProjectSettings",array(array("key"=>"ProdDeployment","value"=>"true"),array("key"=>"CompleteBatch","value"=>"true"),array("key"=>"Env","value"=>$env)));
					else if ($this->_http->getParam("ProdDeployment") == "true")
						$this->_generic->forward("SLS_Bo","ProductionDeployment");
				}
			}
			if (!empty($errors) && $this->_http->getParam("ping") != "true")
			{
				$xml->startTag('errors');
				foreach ($errors as $error)
				{
					$xml->addFullTag('error', $error);
				}
				$xml->endTag('errors');
			}
			if ($this->_http->getParam("ping") == "true")
			{
				$smtp = new SLS_Email("");
				$verdict = $smtp->pingConnection($postHost,$postPort,$postUser,$postPass);			
				$xml->addFullTag("ping",($verdict===true) ? "true" : $verdict,true);
			}
		}
		$this->_generic->eraseCache('Mail');
		$xml->startTag("current_values");
			$xml->addFullTag("host", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($mailXML->getTags("//mails/host")), $this->_generic->getSiteConfig("privateKey")) : SLS_Security::getInstance()->decrypt($this->_generic->getMailConfig("host"), $this->_generic->getSiteConfig("privateKey")), true);
			$xml->addFullTag("port", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($mailXML->getTags("//mails/port")), $this->_generic->getSiteConfig("privateKey")) : SLS_Security::getInstance()->decrypt($this->_generic->getMailConfig("port"), $this->_generic->getSiteConfig("privateKey")), true);
			$xml->addFullTag("user", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? SLS_Security::getInstance()->decrypt(array_shift($mailXML->getTags("//mails/username")), $this->_generic->getSiteConfig("privateKey")) : SLS_Security::getInstance()->decrypt($this->_generic->getMailConfig("username"), $this->_generic->getSiteConfig("privateKey")), true);
			$xml->addFullTag("pass", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/password")) : $this->_generic->getMailConfig("password"), true);
			$xml->addFullTag("domain", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultDomain")) : $this->_generic->getMailConfig("defaultDomain"), true);
			$xml->addFullTag("return", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultReturn")) : $this->_generic->getMailConfig("defaultReturn"), true);
			$xml->addFullTag("nameReturn", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultNameReturn")) : $this->_generic->getMailConfig("defaultNameReturn"), true);
			$xml->addFullTag("reply", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultReply")) : $this->_generic->getMailConfig("defaultReply"), true);
			$xml->addFullTag("nameReply", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultNameReply")) : $this->_generic->getMailConfig("defaultNameReply"), true);
			$xml->addFullTag("sender", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultSender")) : $this->_generic->getMailConfig("defaultSender"), true);
			$xml->addFullTag("nameSender", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/mail_".$env.".xml")) ? array_shift($mailXML->getTags("//mails/defaultNameSender")) : $this->_generic->getMailConfig("defaultNameSender"), true);
		$xml->endTag("current_values");
		
		$xml->addFullTag("url_mail_templates",$this->_generic->getFullPath("SLS_Bo","MailTemplates"),true);
		
		$this->saveXML($xml);		
	}
	
}
?>