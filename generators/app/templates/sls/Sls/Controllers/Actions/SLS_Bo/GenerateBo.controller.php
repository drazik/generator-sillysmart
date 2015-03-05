<?php
/**
* Class GenerateBo into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoGenerateBo extends SLS_BoControllerProtected
{
	public function action()
	{
		set_time_limit(0);
		
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$controllersXML = $this->_generic->getControllersXML();
		$controller = $controllersXML->getTag("//controllers/controller[@isBo='true']/@name");
		$tokenSecret = sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3));
		$xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
		$xmlType = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml"));
		
		// Check if bo controller already exist
		if (empty($controller))
		{
			$xml->startTag("errors");
			$xml->addFullTag("error","Back-office controller could not be found. Please follow the following link to create it.",true);
			$xml->endTag("errors");
		}
		// Else, let's choose the models
		else 
		{
			// If reload
			if ($this->_http->getParam("reload") == "true")
			{
				$modelsWanted = $this->_http->getParam("models");
				$langs = $this->_lang->getSiteLangs();
				
				if (is_array($modelsWanted))
				{
					// Foreach models choose, generate files
					foreach($modelsWanted as $model)
					{
						$db = Sls_String::substrBeforeFirstDelimiter($model,".");
						$table = Sls_String::substrAfterFirstDelimiter($model,".");
						
						# Node into bo.xml
						$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($db."_".$table)."']";
						$boExists = $xmlBo->getTag($boPath."/@type");
						if (empty($boExists))
							$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($db."_".$table)."']";
						$boExists = $xmlBo->getTag($boPath);
						if (empty($boExists))
						{
							$this->_generic->useModel(SLS_String::tableToClass($table),ucfirst(strtolower($db)),"user");
							$class = ucfirst(strtolower($db))."_".SLS_String::tableToClass($table);
							$object = new $class();
							$xmlNode = '    <entry type="table" name="'.strtolower($db."_".$table).'" multilanguage="'.(($object->isMultilanguage()) ? "true" : "false").'">'."\n";
							$xmlNode .= '        <columns>'."\n";
							foreach($object->getColumns() as $column)
							{
								// Avoid pk
								$isPk = ($column == $object->getPrimaryKey() || $column == 'pk_lang') ? true : false;
								// Avoid fk
								$fkExist = $xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($db."_".$table)."' and @columnFk='".$column."']/@tablePk");
								$isFk = (!empty($fkExist)) ? true : false;
								// Avoid quick edit on type file
								$fileExist = $xmlType->getTag("//sls_configs/entry[@table='".strtolower($db."_".$table)."' and @column='".$column."' and (@type='file_all' or @type='file_img')]/@column");
								$isFile = (!empty($fileExist)) ? true : false;
								
								$xmlNode .= '            <column table="'.strtolower($db."_".$table).'" name="'.$column.'" multilanguage="'.(($object->isMultilanguage() && !$isPk) ? "true" : "false").'" displayFilter="true" displayList="'.(($isFk) ? "false" : "true").'" allowEdit="'.(($isPk || $isFk || $isFile) ? "false" : "true").'" allowHtml="false" />'."\n";
							}
							$xmlNode .= '        </columns>'."\n";
							$xmlNode .= '    </entry>'."\n";
							$xmlBo->appendXMLNode("//sls_configs",$xmlNode);
							$xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml",$xmlBo->getXML());
							$xmlBo->refresh();
						}
						# /Node into bo.xml
						
						# BoActions
						$boActions = array("List","Add","Modify","Clone","Delete");
						foreach($boActions as $boAction)
						{
							// Generate Action
							$action = ucfirst(strtolower($boAction)).ucfirst(strtolower($db))."_".SLS_String::tableToClass($table);
							$params = array(0 => array("key" 	=> "reload",
									  				   "value" 	=> "true"),
									  		1 => array("key" 	=> "Controller",
									  				   "value" 	=> $controller),
										 	2 => array("key" 	=> "actionName",
									  				   "value" 	=> $action),
									  		3 => array("key"	=> "token",
									  				   "value"	=> $tokenSecret),
									  		4 => array("key"	=> "template",
									  				   "value" 	=> "bo"),
									  		5 => array("key"	=> "dynamic",
									  				   "value" 	=> "on"),
									  		6 => array("key"	=> "indexes",
									  				   "value"	=> "noindex,nofollow")
										    );
							foreach($langs as $lang)
							{
								$tmpParam = array("key" 	=> $lang."-action",
												  "value" 	=> $action."_".$lang);
								$tmpTitle = array("key" 	=> $lang."-title",
												  "value" 	=> $action);
								array_push($params,$tmpParam);
								array_push($params,$tmpTitle);
							}
							file_get_contents($this->_generic->getFullPath("SLS_Bo",
																		  "AddAction",
																		  $params,
																		  true));
							
							// Erase Action
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/".$boAction."{{DB}}_{{TABLE}}.controller.php"))
							{
								$source = str_replace(array("{{USER_BO}}","{{DB}}","{{TABLE}}"),array($controller,ucfirst(strtolower($db)),SLS_String::tableToClass($table)),file_get_contents($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/".$boAction."{{DB}}_{{TABLE}}.controller.php"));
								file_put_contents($this->_generic->getPathConfig("actionsControllers").$controller."/".$action.".controller.php",$source);
							}
							
							// Erase View Head
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Headers/{{USER_BO}}/".$boAction."{{DB}}_{{TABLE}}.xsl"))
							{
								$source = str_replace(array("{{USER_BO}}","{{DB}}","{{TABLE}}"),array($controller,ucfirst(strtolower($db)),SLS_String::tableToClass($table)),file_get_contents($this->_generic->getPathConfig("installDeployement")."Views/Headers/{{USER_BO}}/".$boAction."{{DB}}_{{TABLE}}.xsl"));
								file_put_contents($this->_generic->getPathConfig("viewsHeaders").$controller."/".$action.".xsl",$source);
							}
							
							// Erase View Body
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Body/{{USER_BO}}/".$boAction."{{DB}}_{{TABLE}}.xsl"))
							{
								$source = str_replace(array("{{USER_BO}}","{{DB}}","{{TABLE}}"),array($controller,ucfirst(strtolower($db)),SLS_String::tableToClass($table)),file_get_contents($this->_generic->getPathConfig("installDeployement")."Views/Body/{{USER_BO}}/".$boAction."{{DB}}_{{TABLE}}.xsl"));
								file_put_contents($this->_generic->getPathConfig("viewsBody").$controller."/".$action.".xsl",$source);
							}
						}
						# /BoActions
					}
					
					$this->_generic->forward("SLS_Bo","ManageRights");
				}
			}
			
			$sql = SLS_Sql::getInstance();
			$models = $this->getAllModels();			
			$dbs = $sql->getDbs();
			sort($dbs,SORT_REGULAR);
						
			$xml->startTag("dbs");
			foreach($dbs as $db)
			{
				sort($models,SORT_REGULAR);
				
				$xml->startTag("db");
				$xml->addFullTag("name",$db,true);
				$xml->startTag("models");
				for($i=0 ; $i<$count=count($models) ; $i++)
				{
					if (SLS_String::startsWith($models[$i],$db))
					{
						$xml->startTag("model");
						$xml->addFullTag("name",SLS_String::substrAfterFirstDelimiter($models[$i],"."),true);
						$xml->addFullTag("existed",($this->boActionExist(SLS_String::substrAfterFirstDelimiter($models[$i],"."),SLS_String::substrBeforeFirstDelimiter($models[$i],"."))) ? "true" : "false",true);
						$xml->endTag("model");
					}
				}
				$xml->endTag("models");
				$xml->endTag("db");
			}
			$xml->endTag("dbs");
		}
		
		$xml->addFullTag("url_add_controller",$this->_generic->getFullPath("SLS_Bo","AddController",array(0=>array("key"=>"isBo","value"=>"true"))),true);
		$this->saveXML($xml);
	}
}
?>