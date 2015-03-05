<?php
class SLS_BoBo extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		// Objects
		$xml = $this->getXML();
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		$this->_generic->registerLink('Generate', 'SLS_Bo', 'GenerateBo');
		$this->_generic->registerLink('Translation', 'SLS_Bo', 'GenerateBoAction', array("Actions" => "Boi18n"));
		$this->_generic->registerLink('FileUpload', 'SLS_Bo', 'GenerateBoAction', array("Actions" => "BoFileUpload"));
		$this->_generic->registerLink('ManageAdmin', 'SLS_Bo', 'GenerateBoAction', array("Actions" => "BoUserList|BoUserAdd|BoUserDelete|BoUserModify|BoUserStatus"));
		$this->_generic->registerLink('ProjectSettings', 'SLS_Bo', 'GenerateBoAction', array("Actions" => "BoProjectSettings"));
		$this->_generic->registerLink('Manage_Rights', 'SLS_Bo', 'ManageRights');
		$this->_generic->registerLink('Manage_BoMenu', 'SLS_Bo', 'BoMenu');
		$xml->addFullTag("delete",$this->_generic->getFullPath("SLS_Bo","DeleteBo",array(),false));
		$xml->addFullTag("edit",$this->_generic->getFullPath("SLS_Bo","EditBo",array(),false));
		$this->_xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$bos = array();
		
		// Search for user back-office
		$controllersXML = $this->_generic->getControllersXML();
		$controller = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/@name"));
		if (!empty($controller))
		{
			$models = $this->getAllModels();			
			for($i=0 ; $i<$count=count($models) ; $i++)
			{
				$model 	= SLS_String::substrAfterFirstDelimiter($models[$i],".");
				$db 	= SLS_String::substrBeforeFirstDelimiter($models[$i],".");
				if ($this->boActionExist($model,$db))
				{
					$this->_generic->useModel($model,$db,"user");
					$class = ucfirst($db)."_".SLS_String::tableToClass($model);
					$object = new $class();
					$bos[$class] = array("db" 			=> $db,
									 	 "className" 	=> $class,
									 	 "tableName" 	=> $object->getTable(),											 	
									 	 "nb_actions" 	=> count($this->getActionsBo($model,$db)));
				}
			}	
		}		
		
		asort($bos,SORT_REGULAR);
		
		$xml->startTag("bos");
		foreach($bos as $bo)
		{
			$categoryExists = $this->_xmlBo->getTag("//sls_configs/entry[@type='category' and entry[@type='table' and @name='".strtolower($bo["db"]."_".$bo["tableName"])."']]/@name");
			
			$xml->startTag("bo");
			$xml->addFullTag("db",strtolower($bo["db"]),true);
			$xml->addFullTag("class",$bo["className"],true);
			$xml->addFullTag("table",$bo["tableName"],true);
			$xml->addFullTag("category",(empty($categoryExists)) ? "X" : $categoryExists,true);
			$xml->addFullTag("nb_actions",$bo["nb_actions"],true);
			$xml->endTag("bo");
		}
		$xml->endTag("bos");		
		
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml");
		$xmlRights = new SLS_XMLToolbox($pathsHandle);
		$result = $xmlRights->getTags("//sls_configs/entry");
		$xml->addFullTag("admins_exist",(!empty($result)) ? "true" : "false",true);
		
		$xml->startTag("actions");
		$action = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='Boi18n']"));
		$action2 = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='BoFileUpload']"));
		$action3 = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='BoUserList']"));
		$action4 = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='BoProjectSettings']"));
		$xml->startTag("action");
			$xml->addFullTag("name","Translation",true);
			$xml->addFullTag("icon","boi18n16.png",true);
			$xml->addFullTag("existed",(!empty($action)) ? "true" : "false",true);
		$xml->endTag("action");
		$xml->startTag("action");
			$xml->addFullTag("name","FileUpload",true);
			$xml->addFullTag("icon","boupload16.png",true);
			$xml->addFullTag("existed",(!empty($action2)) ? "true" : "false",true);
		$xml->endTag("action");
		$xml->startTag("action");
			$xml->addFullTag("name","ManageAdmin",true);
			$xml->addFullTag("icon","boadmin16.png",true);
			$xml->addFullTag("existed",(!empty($action3)) ? "true" : "false",true);
		$xml->endTag("action");
		$xml->startTag("action");
			$xml->addFullTag("name","ProjectSettings",true);
			$xml->addFullTag("icon","bosettings16.png",true);
			$xml->addFullTag("existed",(!empty($action4)) ? "true" : "false",true);
		$xml->endTag("action");
		$xml->endTag("actions");
		if (SLS_Sql::getInstance()->tableExists("sls_graph"))
		{
			$this->_generic->useModel("Sls_graph",$this->defaultDb,"sls");
			$className = ucfirst($this->defaultDb)."_Sls_graph";
			$slsGraph = new $className;
			$nbGraph = $slsGraph->countModels("sls_graph");
		}
		else
			$nbGraph = 0;
		$xml->addFullTag("nb_reporting",$nbGraph,true);
		$xml->addFullTag("url_reporting",$this->_generic->getFullPath("SLS_Bo","ReportingBo"),true);
		
		$this->saveXML($xml);
	}
	
}
?>