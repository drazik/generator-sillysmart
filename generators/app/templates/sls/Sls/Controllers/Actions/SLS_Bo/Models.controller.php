<?php
class SLS_BoModels extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		// Objects
		$xml = $this->getXML();
		$models = array();

		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		
		// Actions
		$this->_generic->registerLink('Generate', 'SLS_Bo', 'GenerateModels');
		$this->_generic->registerLink('Properties', 'SLS_Bo', 'ModelsProperties');
		$xml->addFullTag("delete",$this->_generic->getFullPath("SLS_Bo","DeleteModel",array(),false));
		$xml->addFullTag("edit",$this->_generic->getFullPath("SLS_Bo","EditModel",array(),false));
		
		// Get the existing models		
		$handle = opendir($this->_generic->getPathConfig("models"));
		
		// Foreach models 
		while (false !== ($file = readdir($handle))) 
		{
			if (!is_dir($this->_generic->getPathConfig("models")."/".$file) && substr($file, 0, 1) != ".") 
			{
				$fileExploded = explode(".",$file);
				if (is_array($fileExploded) && count($fileExploded) == 4)
				{ 
					$db = $fileExploded[0];
					$class = $fileExploded[1];
					$className = $db."_".$class;
					$this->_generic->useModel($class,$db,"user");
					$object = new $className();
					$models[$className] = array("db" 			=> $db,
											 	"className" 	=> $className,
											 	"tableName" 	=> $object->getTable(),
											 	"primaryKey" 	=> $object->getPrimaryKey(),
											 	"nbColumns" 	=> count($object->getParams()));
					
				}
			}
		}
		
		asort($models,SORT_REGULAR);
		
		$xml->startTag("models");
		foreach($models as $model)
		{			
			$xml->startTag("model");
			$xml->addFullTag("db",strtolower($model["db"]),true);
			$xml->addFullTag("class",$model["className"],true);
			$xml->addFullTag("table",$model["tableName"],true);
			$xml->addFullTag("pk",$model["primaryKey"],true);
			$xml->addFullTag("nbColumns",$model["nbColumns"],true);
			$xml->addFullTag("up_to_date",($this->isUpToDate($model["tableName"],strtolower($model["db"]))) ? "true" : "false",true);
			$xml->addFullTag("url_update",$this->_generic->getFullPath("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>strtolower($model["db"])."_".$model["tableName"]),array("key"=>"Redirect","value"=>$this->_generic->getActionId()))),true);
			$xml->addFullTag("url_flush_cache",$this->_generic->getFullPath("SLS_Bo","FlushCache",array(array("key"=>"From","value"=>"Table"),array("key"=>"Item","value"=>$model["tableName"]))),true);
			$xml->endTag("model");
		}
		$xml->endTag("models");
		
		$this->saveXML($xml);
	}
	
	public function isUpToDate($table,$db)
	{
		$contentM = $this->getModelSource($table,$db);		
		$currentContent = file_get_contents($this->_generic->getPathConfig("models").ucfirst($db).".".SLS_String::tableToClass($table).".model.php");
		return ($currentContent == $contentM) ? true : false; 
	}
}
?>