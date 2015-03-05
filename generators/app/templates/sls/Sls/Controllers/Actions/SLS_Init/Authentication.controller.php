<?php
class SLS_InitAuthentication extends SLS_InitControllerProtected 
{		
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$this->secureURL();
		
		$this->_generic->registerLink('authentication', 'SLS_Init', 'Authentication');
		$reload = $this->_http->getParam('authentication_reload');
		$login = SLS_String::trimSlashesFromString($this->_http->getParam('auth_login'));
		$pass1 =  SLS_String::trimSlashesFromString($this->_http->getParam('auth_pass1'));
		$pass2 = SLS_String::trimSlashesFromString($this->_http->getParam('auth_pass2'));
		$xml = $this->getXML();
		$errors = array();
		if ($reload == 'true')
		{
			if (strlen($login) < 6)
				array_push($errors, "The administrator username should have 6 caracters at least");
			if (strlen($pass1) < 6)
				array_push($errors, "The password should have 6 caracters at least");
			if ($pass1 != $pass2)
				array_push($errors, "Both password must be the same");
			
			if (empty($errors))
			{
				if (!is_file($this->_generic->getPathConfig('configSls')."sls.xml") && !touch($this->_generic->getPathConfig('configSls')."sls.xml"))
					$this->_generic->dispatch('SLS_Init', 'DirRights');
				else 
				{
					$coreXml = $this->_generic->getCoreXml('sls');
					$user = new SLS_XMLToolbox(false);
					$user->startTag("user", array("login"=>sha1($login),"pass"=>sha1($pass1),"level"=>"0"));
					$user->endTag("user");
					$coreXml->appendXMLNode("//sls_configs/auth/users", $user->getXML('noHeader'));
					file_put_contents($this->_generic->getPathConfig("configSls")."sls.xml", $coreXml->getXML());
					$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"GlobalSettings",1=>"Settings"));
					return $this->_generic->dispatch("SLS_Init", "GlobalSettings");
				}
			}
			else 
			{				
				$xml->startTag("errors");
				foreach ($errors as $error)
					$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
			}
		}
		$this->saveXML($xml);
	}
	
}
?>