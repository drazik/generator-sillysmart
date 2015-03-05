<?php
class SLS_BoProductionDeployment extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$environments = $this->getEnvironments();
				
		$xml->startTag("environments");		
		for($i=0 ; $i<$count=count($environments) ; $i++)
		{
			$prodFiles = array ("site" 		=> false,
								"db" 		=> false,
								"mail" 		=> false,
								"project" 	=> false);
			foreach($prodFiles as $prodFile => $exists)
			{
				$ok = false;
				if (file_exists($this->_generic->getPathConfig("configSecure").$prodFile."_".$environments[$i].".xml"))
				{
					$handle = file_get_contents($this->_generic->getPathConfig("configSecure").$prodFile."_".$environments[$i].".xml");
					$xmlConf = new SLS_XMLToolbox($handle);
					if ($prodFile == "site" && $xmlConf->getTag("//configs/domainName") != "")
						$ok = true;
					if ($prodFile == "db" && $xmlConf->getTag("//dbs/db/host") != "")
						$ok = true;
					if ($prodFile == "mail" && $xmlConf->getTag("//mails/host") != "")
						$ok = true;
					if ($prodFile == "project" && $handle != "")
						$ok = true;
				}
				$prodFiles[$prodFile] = $ok;
			}
			$xml->startTag("environment");
			$xml->addFullTag("environment_title",$environments[$i],true);
			$xml->startTag("prod_files");
			foreach($prodFiles as $prodFile => $exists)
			{
				$action = "";
				switch($prodFile)
				{
					case "site": 	$action = "GlobalSettings"; 	break;
					case "db": 		$action = "DataBaseSettings"; 	break;
					case "mail": 	$action = "MailSettings"; 		break;
					case "project": $action = "ProjectSettings"; 	break;
				}
				$xml->startTag("prod_file");
					$xml->addFullTag("title",$prodFile."_".$environments[$i].".xml",true);
					$xml->addFullTag("exists",($exists) ? "true" : "false",true);
					$xml->addFullTag("url_edit",$this->_generic->getFullPath("SLS_Bo",$action,array(array("key"=>"ProdDeployment","value"=>"true"),array("key"=>"Env","value"=>$environments[$i]))),true);
				$xml->endTag("prod_file");
			}
			$xml->endTag("prod_files");
			
			$xml->addFullTag("url_batch",$this->_generic->getFullPath("SLS_Bo","GlobalSettings",array(array("key"=>"ProdDeployment","value"=>"true"),array("key"=>"CompleteBatch","value"=>"true"),array("key"=>"Env","value"=>$environments[$i]))),true);
			$xml->endTag("environment");
		}
		$xml->endTag("environments");
		
		$xml->addFullTag("url_add_environment",$this->_generic->getFullPath("SLS_Bo","EnvironmentsAdd"),true);
		
		$this->saveXML($xml);		
	}
	
}
?>