<?php
class SLS_BoManageRights extends SLS_BoControllerProtected 
{
	public function action() 
	{		
		// Objects
		$xml = $this->getXML();
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		$this->_generic->registerLink('Add', 'SLS_Bo', 'AddRight');
		$xml->addFullTag("delete",$this->_generic->getFullPath("SLS_Bo","DeleteRight",array(),false));
		$xml->addFullTag("edit",$this->_generic->getFullPath("SLS_Bo","EditRight",array(),false));
		
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml");		
		$xmlRights = new SLS_XMLToolbox($pathsHandle);
		$result = $xmlRights->getTagsAttributes("//sls_configs/entry",array("login","password","name","firstname","enabled"));
				
		$xml->startTag("accounts");
		for($i=0 ; $i<$count=count($result) ; $i++)
		{
			$xml->startTag("account");
			for($j=0 ; $j<$count2=count($result[$i]["attributes"]) ; $j++)
			{				
				if ($result[$i]["attributes"][$j]["key"] == "password")
					$xml->addFullTag($result[$i]["attributes"][$j]["key"],"******",true);
				else if (SLS_String::contains($result[$i]["attributes"][$j]["key"],"name"))
					$xml->addFullTag($result[$i]["attributes"][$j]["key"],ucwords(strtolower($result[$i]["attributes"][$j]["value"])),true);
				else
					$xml->addFullTag($result[$i]["attributes"][$j]["key"],$result[$i]["attributes"][$j]["value"],true);
			}
			$xml->addFullTag("color",$xmlRights->getTag("//sls_configs/entry[@login='".$result[$i]["attributes"][0]["value"]."']/settings/setting[@key='color']"),true);
			$xml->endTag("account");
		}
		$xml->endTag("accounts");
		
		$xml->addFullTag("url_status",$this->_generic->getFullPath("SLS_Bo","ManageRightsStatus",array(),false));
		
		$this->saveXML($xml);
	}
	
}
?>