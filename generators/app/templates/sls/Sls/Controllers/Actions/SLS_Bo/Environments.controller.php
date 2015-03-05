<?php
class SLS_BoEnvironments extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$environments = $this->getEnvironments();
				
		$xml->startTag("environments");
		foreach($environments as $environment)
		{
			$xml->startTag("environment");
				$xml->addFullTag("title",$environment,true);
				$xml->addFullTag("url_setting",$this->_generic->getFullPath("SLS_Bo","ProductionDeployment",array(array("key"=>"Item","value"=>$environment))),true);
				$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","EnvironmentsDelete",array(array("key"=>"Item","value"=>$environment))),true);
			$xml->endTag("environment");
		}
		$xml->endTag("environments");
		
		$xml->addFullTag("url_add",$this->_generic->getFullPath("SLS_Bo","EnvironmentsAdd"),true);
		
		$this->saveXML($xml);		
	}
	
}
?>