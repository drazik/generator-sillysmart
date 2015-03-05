<?php
class SLS_BoIndex extends SLS_BoControllerProtected 
{
	
	public function action() 
	{
		$xml = $this->getXML();
		$this->_generic->registerLink('Authentication', 'SLS_Bo', 'Index');
		$controllers = $this->_generic->getControllersXML();
		
		$reload = $this->_http->getParam('reload');
		if ($reload == 'true')
		{
			$errors = array();
			$login = $this->_http->getParam('login');
			$pass = $this->_http->getParam('password');
			if (empty($login))
				array_push($errors, "Fill your username");
			if (empty($pass))
				array_push($errors, "Fill your password");
				
			// If no errors, we check the ID
			if (empty($errors))
			{
				$password = array_shift($this->_generic->getCoreXml('sls')->getTags("//sls_configs/auth/users/user[@login='".sha1($login)."']/@pass"));				
				if (empty($password))
					array_push($errors, "Your authentication informations are incorrect");
				else 
				{
					if (sha1($pass) != $password)	
						array_push($errors, "Your authentication informations are incorrect");
					else 
					{
						$sessionToken = substr(substr(sha1($this->_generic->getSiteConfig("privateKey")),12,31).substr(sha1($this->_generic->getSiteConfig("privateKey")),4,11),6);
						
						$this->_generic->getObjectSession()->setParam('SLS_SESSION_VALID_'.$sessionToken, 'true');
						$this->_generic->getObjectSession()->setParam('SLS_SESSION_USER_'.$sessionToken, $login);						
						$this->_generic->getObjectSession()->setParam('SLS_SESSION_PASS_'.$sessionToken, $password);
						$this->_generic->getObjectSession()->setParam('SLS_SESSION_LEVEL_'.$sessionToken, array_shift($this->_generic->getCoreXml('sls')->getTags("//sls_configs/auth/users/user[login='".sha1($login)."']/level")));
						
						$redirect = $this->_http->getParam("Redirect");
						$redirectMore = $this->_http->getParam("RedirectMore");
						if ($this->_generic->actionIdExists($redirect))
						{
							$mapping = $this->_generic->translateActionId($redirect);					
							$this->_generic->redirect($this->_generic->getSiteConfig('protocol').'://'.$this->_generic->getSiteConfig('domainName').'/'.$mapping['controller'].'/'.$mapping['scontroller'].((!empty($redirectMore)) ? '/'.str_replace("|","/",$redirectMore) : '').'.'.$this->_generic->getSiteConfig('defaultExtension'));
						}
						else	
							$this->_generic->forward("SLS_Bo","Home");						
					}
				}
			}
			$xml->startTag('errors');
			foreach ($errors as $error)
				$xml->addFullTag('error', $error, true);	
			
			$xml->endTag('errors');
			
		}
		$this->saveXML($xml);
	}
}
?>