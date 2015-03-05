<?php
class SLS_BoDeleteDataBase extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml 	= $this->makeMenu($xml);
		$dbXML 	= $this->_generic->getDbXML();
		
		// Check if db exists
		$alias = rawurldecode($this->_http->getParam("alias"));
		$result = $dbXML->getTagsAttribute("//dbs/db","alias");
		$dbs = array();
		for($i=0 ; $i<$count=count($result) ; $i++)
			array_push($dbs,$result[$i]["attribute"]);
			
		if (in_array($alias,$dbs))
		{
			// Check if it is not the default db
			$result = array_shift($this->_generic->getDbXML()->getTagsAttribute("//dbs/db[@isDefault='true']","alias"));
			if ($alias != $result["attribute"])
			{	
				$files = array();
				$bos = array();
				$xml->addFullTag("db_exists","true",true);
				
				$controllerXML = $this->_generic->getControllersXML();
				$genericBo = array_shift($controllerXML->getTags("//controllers/controller[@isBo='true']/@name"));
				
				// List files which will be deleted
				$models = $this->getAllModels();
				$xml->startTag("models");
				foreach($models as $model)
				{
					if (SLS_String::startsWith($model,$alias))
					{
						$xml->startTag("model");
						$xml->addFullTag("label",SLS_String::substrAfterFirstDelimiter($model,"."),true);
						$xml->addFullTag("file",ucfirst($model),true);
						array_push($files,$this->_generic->getPathConfig("models").ucfirst($model).".model.php");
						array_push($files,$this->_generic->getPathConfig("modelsSql").ucfirst($model).".sql.php");
						
						$actionsBo = $this->getActionsBo(SLS_String::substrAfterFirstDelimiter($model,"."),$alias);
						$xml->startTag("bos");
						foreach($actionsBo as $actionBo)
						{
							$tmp = array("model"=>SLS_String::substrAfterFirstDelimiter($model,"."),"action"=>strtolower(SLS_String::substrBeforeFirstDelimiter($actionBo,ucfirst($alias)."_")),"alias"=>$alias);							
							array_push($bos,$tmp);
							$xml->startTag("bo");
							$xml->addFullTag("label",SLS_String::substrBeforeFirstDelimiter($actionBo,ucfirst($alias)."_"),true);
							$xml->addFullTag("file",$actionBo.".controller.php",true);
							$xml->endTag("bo");
						}
						$xml->endTag("bos");
						$xml->endTag("model");
					}
				}
				$xml->endTag("models");
				
				if ($this->_http->getParam("reload") == "true")
				{
					$password = SLS_String::trimSlashesFromString($this->_http->getParam("password", 'post'));
					$login = SLS_String::trimSlashesFromString($this->_http->getParam("login", 'post'));
					
					$slsXml = $this->_generic->getCoreXML('sls');
					$passXML = array_shift($slsXml->getTags("//sls_configs/auth/users/user[@login='".sha1($login)."' and @level='0']/@pass"));
					
					if (!empty($passXML) && $passXML == sha1($password))
					{						
						// Delete files bo
						foreach($bos as $bo)
							$this->deleteActionBo($bo["model"],$bo["action"],$bo["alias"]);
						// Delete files model
						foreach($files as $file)
							@unlink($file);
						// Delete config
						$dbXML->deleteTags("//dbs/db[@alias='".$alias."']");						
						$dbXML->saveXML($this->_generic->getPathConfig("configSecure")."db.xml");
						
						$controllers = $this->_generic->getTranslatedController("SLS_Bo","DataBaseSettings");
						$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"].".sls");
					}
					else					
						$xml->addFullTag("incorrect_account","true",true);					
				}
				
				
				$xml->addFullTag("database",$alias,true);
			}
			else
			{
				$xml->startTag("errors");
				$xml->addFullTag("error","You can't delete the default database",true);
				$xml->endTag("errors");
			}
		}
		else
		{
			$xml->startTag("errors");
			$xml->addFullTag("error","This database can't be found",true);
			$xml->endTag("errors");
		}
		
		$this->saveXML($xml);
	}	
}
?>