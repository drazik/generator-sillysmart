<?php
class SLS_BoEnvironmentsAdd extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$environments = $this->getEnvironments();
		$files = array("site","db","project","mail");
		$errors = array();
				
		if ($this->_http->getParam("reload") == "true")
		{
			$environment = $this->_http->getParam("environment");
			if (in_array($environment,$environments))			
				$errors[] = "Environment already exists.";
			else if (empty($environment))
				$errors[] = "You must fill your environment name.";
					
			if (empty($errors))
			{
				foreach($files as $file)
					file_put_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/".$file."_".SLS_String::stringToUrl($environment,"-").".xml",file_get_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure").$file.".xml"));
					
				$this->_generic->forward("SLS_Bo","GlobalSettings",array(array("key"=>"ProdDeployment","value"=>"true"),array("key"=>"CompleteBatch","value"=>"true"),array("key"=>"Env","value"=>$environment)));
			}
			else
			{
				$xml->startTag("errors");
				foreach($errors as $error)
					$xml->addFullTag("error",$error,true);
				$xml->endTag("errors");
			}
		}
		
		$this->saveXML($xml);		
	}
	
}
?>