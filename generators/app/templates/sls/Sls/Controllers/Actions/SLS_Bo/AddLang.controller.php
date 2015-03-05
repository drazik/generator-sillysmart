<?php
class SLS_BoAddLang extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml 	= $this->makeMenu($xml);
		$langs 	= array();
		$appli_langs = array();
		$handle = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."international.xml"));
		$step = 0;
		$errors = array();
		$controllerXML = $this->_generic->getControllersXML();
		$metaXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."metas.xml"));
		$appli_langs = $this->_generic->getObjectLang()->getSiteLangs();
		$defaultLang = $this->_generic->getObjectLang()->getDefaultLang();
		
		// If lang have been choose
		if ($this->_http->getParam("step") == "1")
		{
			$lang1 = SLS_String::trimSlashesFromString($this->_http->getParam("lang"));
			$lang  = array_shift($handle->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[node()='".$lang1."']/@iso"));			
			
			if (!in_array($lang,$appli_langs))
			{
				$step = 2;
				$xml->addFullTag("step","2",true);
				$xml->addFullTag("lang_to_add",$lang,true);
			}
			else
			{
				$xml->addFullTag("lang_selected",$lang1,true);
				$xml->startTag("errors");
				$xml->addFullTag("error","This lang already exist in your application",true);
				$xml->endTag("errors");
			}		
		}
		if ($this->_http->getParam("step") == "2" || $step == 2)
		{			
			$lang = (empty($lang)) ? $this->_http->getParam("lang_to_add") : $lang;
			$xml->addFullTag("step","2",true);
			$xml->addFullTag("lang_to_add",$lang,true);
			
			$generics = array(	"MONDAY" 							=> "",
								"TUESDAY" 							=> "",
								"WEDNESDAY" 						=> "",
								"THURSDAY" 							=> "",
								"FRIDAY" 							=> "",
								"SATURDAY" 							=> "",
								"SUNDAY" 							=> "",
								"JANUARY" 							=> "",
								"FEBRUARY" 							=> "",
								"MARCH" 							=> "",
								"APRIL" 							=> "",
								"MAY" 								=> "",
								"JUNE" 								=> "",
								"JULY" 								=> "",
								"AUGUST" 							=> "",
								"SEPTEMBER" 						=> "",
								"OCTOBER" 							=> "",
								"NOVEMBER" 							=> "",
								"DECEMBER" 							=> "",
								"DATE_PATTERN_TIME" 				=> "",
								"DATE_PATTERN_FULL_TIME" 			=> "",
								"DATE_PATTERN_DATE" 				=> "",
								"DATE_PATTERN_MONTH_LITTERAL" 		=> "",
								"DATE_PATTERN_FULL_LITTERAL" 		=> "",
								"DATE_PATTERN_FULL_LITTERAL_TIME" 	=> "",
								"DATE_PATTERN_MONTH_LITTERAL_TIME" 	=> "",
								"DATE_DIFF_Y"						=> "",
								"DATE_DIFF_M"						=> "",
								"DATE_DIFF_W"						=> "",
								"DATE_DIFF_D"						=> "",
								"DATE_DIFF_H"						=> "",
								"DATE_DIFF_I"						=> "",
								"DATE_DIFF_S"						=> "",
								"E_CONTENT" 						=> "",
								"E_EMPTY" 							=> "",
								"E_KEY" 							=> "",
								"E_COMPLEXITY" 						=> "",
								"E_LENGTH" 							=> "",
								"E_NULL" 							=> "",
								"E_UNIQUE" 							=> "",
								"E_TYPE" 							=> "",
								"E_SIZE" 							=> "",
								"E_EXIST" 							=> "",
								"E_WRITE" 							=> "",
								"E_LOGGED" 							=> "",
								"E_AUTHORIZED" 						=> ""
							);									
			// Try to recover generic langs from Deployement
			if (file_exists($this->_generic->getPathConfig("installDeployement")."Langs/Generics/generic.".$lang.".lang.php"))
				include($this->_generic->getPathConfig("installDeployement")."Langs/Generics/generic.".$lang.".lang.php");			
			// Set the default value by english or current lang if has been found in Deployement
			foreach($generics as $key => $value)			
				$generics[$key] = SLS_String::trimSlashesFromString($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_'.$key]);
			
			$controllers = array();
				
			// Recover all controllers
			$controllersA = $controllerXML->getTags("//controllers/controller[@side='user']/@name");
			foreach($controllersA as $controller)
			{
				$id = array_shift($controllerXML->getTags("//controllers/controller[@name='".$controller."']/@id"));
				
				$values = array();
				foreach($appli_langs as $appli_lang)				
					$values[$appli_lang] = array_shift($controllerXML->getTags("//controllers/controller[@name='".$controller."']/controllerLangs/controllerLang[@lang='".$appli_lang."']"));					
				$values[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam($id."_".$lang));
				
				$result = array("id" => $id, "key" => $controller, "values" => $values, "actions" => array());
				array_push($controllers,$result);
			}
			
			// Foreach controllers, recover all actions
			for($i=0 ; $i<$count=count($controllers) ; $i++)
			{
				$controller = $controllers[$i]["key"];
				$actions = $controllerXML->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller/@name");
				$actionsA = array();
				
				foreach($actions as $action)
				{
					$id = array_shift($controllerXML->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."']/@id"));
					
					$values = array();
					foreach($appli_langs as $appli_lang)				
						$values[$appli_lang] = array_shift($controllerXML->getTags("//controllers/controller[@name='".$controller."']/scontrollers/scontroller[@name='".$action."']/scontrollerLangs/scontrollerLang[@lang='".$appli_lang."']"));
					$values[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam($id."_".$lang));
					
					$metas = array("title" => array(), "description" => array(), "keywords" => array());
					$titles = array();
					$descriptions = array();
					$keywords = array();
					foreach($appli_langs as $appli_lang)
					{	
						$titles[$appli_lang] = array_shift($metaXML->getTags("//sls_configs/action[@id='".$id."']/title[@lang='".$appli_lang."']"));						
						$descriptions[$appli_lang] = array_shift($metaXML->getTags("//sls_configs/action[@id='".$id."']/description[@lang='".$appli_lang."']"));						
						$keywords[$appli_lang] = array_shift($metaXML->getTags("//sls_configs/action[@id='".$id."']/keywords[@lang='".$appli_lang."']"));										
					}
					$titles[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam("meta_title-".$id."_".$lang));
					$descriptions[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam("meta_description-".$id."_".$lang));
					$keywords[$lang] = SLS_String::trimSlashesFromString($this->_http->getParam("meta_keywords-".$id."_".$lang));
					$metas["title"] = $titles;
					$metas["description"] = $descriptions;
					$metas["keywords"] = $keywords;
					
					$result = array("id" => $id,"key" => $action, "values" => $values, "metas" => $metas);
					array_push($actionsA,$result);
				}
				$controllers[$i]["actions"] = $actionsA;
			}
			
			// If informations have been sent, check if it's good
			if ($this->_http->getParam("reload") == "true")
			{
				$mods = array();
				$smods = array();
				
				// Get all controllers
				foreach($this->_http->getParams() as $key => $value)
				{
					// Controllers case
					if (SLS_String::startsWith($key,"c_"))
					{
						$id = SLS_String::substrBeforeLastDelimiter($key,"_".$lang);
						$mod = array_shift($controllerXML->getTags("//controllers/controller[@id='".$id."']/@name"));
						$mods[$id] = SLS_String::stringToUrl(SLS_String::trimSlashesFromString($value),"",false);						
						if (empty($mods[$id]))
							array_push($errors,"You have to fill the rewrite of the controller '".$mod."'.");
						$modsExisted = $controllerXML->getTags("//controllers/controller/controllerLangs/controllerLang");
						if (in_array($mods[$id],$modsExisted))
							array_push($errors,"The name for rewrite of the controller '".$mod."' is already used, please choose another one.");
					}
					// Actions case
					else if (SLS_String::startsWith($key,"a_"))
					{
						$id = SLS_String::substrBeforeLastDelimiter($key,"_".$lang);
						$smod = array_shift($controllerXML->getTags("//controllers/controller/scontrollers/scontroller[@id='".$id."']/@name"));
						$mod = array_shift($controllerXML->getTags("//controllers/controller[scontrollers/scontroller[@id='".$id."']]/@name"));
						$idC = array_shift($controllerXML->getTags("//controllers/controller[scontrollers/scontroller[@id='".$id."']]/@id"));
						$smods[$id] = SLS_String::stringToUrl(SLS_String::trimSlashesFromString($value),"",false);						
						if (empty($smods[$id]))
							array_push($errors,"You have to fill the rewrite of the action '".$smod."' (controller '".$mod."').");
						$smodsExisted = $controllerXML->getTags("//controllers/controller[@id='".$idC."']/scontrollers/scontroller/scontrollerLangs/scontrollerLang[@lang='".$lang."']");
						if (in_array($smods[$id],$smodsExisted))
							array_push($errors,"The name for rewrite of the action '".$smod."' (controller '".$mod."') is already used by another action of the same controller, please choose another one.");
					}
					// Generics case
					else if (SLS_String::startsWith($key,"generic_"))
					{
						$label = SLS_String::substrAfterFirstDelimiter($key,"generic_");
						if (empty($value))
							array_push($errors,"You have to fill the content of the variable '".$label."'");
						$generics[$label] = strtolower(SLS_String::trimSlashesFromString($value));
					}
				}
				
				// If all cool
				if (empty($errors))
				{
					// controllers.xml
					foreach($mods as $key => $value)
					{
						$str = '<controllerLang lang="'.$lang.'"><![CDATA['.$value.']]></controllerLang>';
						$controllerXML->appendXMLNode("//controllers/controller[@id='".$key."']/controllerLangs",$str);
					}
					foreach($smods as $key => $value)
					{
						$str = '<scontrollerLang lang="'.$lang.'"><![CDATA['.$value.']]></scontrollerLang>';
						$controllerXML->appendXMLNode("//controllers/controller/scontrollers/scontroller[@id='".$key."']/scontrollerLangs",$str);
						
						// metas.xml
						$title = SLS_String::trimSlashesFromString($this->_http->getParam("meta_title-".$key."_".$lang));
						if (empty($title))
							$str = '<title lang="'.$lang.'"/>';
						else 
							$str = '<title lang="'.$lang.'"><![CDATA['.$title.']]></title>';
						$metaXML->appendXMLNode("//sls_configs/action[@id='".$key."']",$str);
						$description = SLS_String::trimSlashesFromString($this->_http->getParam("meta_description-".$key."_".$lang));
						if (empty($description))
							$str = '<description lang="'.$lang.'"/>';
						else 
							$str = '<description lang="'.$lang.'"><![CDATA['.$description.']]></description>';
						$metaXML->appendXMLNode("//sls_configs/action[@id='".$key."']",$str);
						$keyword = SLS_String::trimSlashesFromString($this->_http->getParam("meta_keywords-".$key."_".$lang));
						if (empty($keyword))
							$str = '<keywords lang="'.$lang.'"/>';
						else 
							$str = '<keywords lang="'.$lang.'"><![CDATA['.$keyword.']]></keywords>';
						$metaXML->appendXMLNode("//sls_configs/action[@id='".$key."']",$str);
						// /metas.xml
					}
					$controllerXML->saveXML($this->_generic->getPathConfig("configSecure")."controllers.xml");
					$metaXML->saveXML($this->_generic->getPathConfig("configSls")."metas.xml");
					// /controllers.xml
					
					// site.xml
					$siteXML = $this->_generic->getSiteXML();
					$str = '<name isSecure="false" js="false" active="false"><![CDATA['.$lang.']]></name>';
					$siteXML->appendXMLNode("//configs/langs",$str);
					$siteXML->saveXML($this->_generic->getPathConfig("configSecure")."site.xml");
					// /site.xml
					
					// generic.iso.lang.php
					$fileContent = '<?php'."\n".
								   '/**'."\n".
								   ' * Generic Sls Vars'."\n".
								   ' */'."\n";
					foreach($generics as $key => $value)					
						$fileContent .= '$GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'SLS_'.$key.'\'] = "'.str_replace('"','\"',$value).'";'."\n";
					$fileContent .= '?>';
					file_put_contents($this->_generic->getPathConfig("coreGenericLangs")."generic.".$lang.".lang.php",$fileContent);
					// /generic.iso.lang.php
					
					// site.iso.lang.php
					$fileContent =  '<?php'."\n".
									'/**'."\n".
									' * SillySmart Translations'."\n".
									' * Language : '.array_shift($handle->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[node()='".$lang."']/@iso")).' ('.strtoupper($lang).')'."\n".
									' */'."\n\n".
									'?>';
					file_put_contents($this->_generic->getPathConfig("genericLangs")."site.".$lang.".lang.php",str_replace(array("Translations in '".strtoupper($defaultLang)."'","site.".$defaultLang.".lang.php","sentence in ".strtoupper($defaultLang)),array("Translations in '".strtoupper($lang)."'","site.".$lang.".lang.php","sentence in ".strtoupper($lang)),file_get_contents($this->_generic->getPathConfig("genericLangs")."site.".$defaultLang.".lang.php")));
					// /site.iso.lang.php
										
					// Actions langs files
					$directories = array();
					foreach($mods as $key => $value)					
						array_push($directories,array_shift($controllerXML->getTags("//controllers/controller[@id='".$key."']/@name")));
					$this->copyActionsLang($directories,$lang);
					// /Actions langs files
					
					// User_Bo langs
					$controllerBo = $controllerXML->getTag("//controllers/controller[@side='user' and @isBo='true']/@name");
					if (!empty($controllerBo))
					{
						if (file_exists($this->_generic->getPathConfig("installDeployement")."Langs/Actions/{{USER_BO}}/__{{USER_BO}}.".$lang.".lang.php"))
							$langContent = str_replace(array("{{USER_BO}}"),array($controllerBo),file_get_contents($this->_generic->getPathConfig("installDeployement")."Langs/Actions/{{USER_BO}}/__{{USER_BO}}.".$lang.".lang.php"));
						else
							$langContent = str_replace(array("{{USER_BO}}"),array($controllerBo),file_get_contents($this->_generic->getPathConfig("installDeployement")."Langs/Actions/{{USER_BO}}/__{{USER_BO}}.en.lang.php"));
						if (!empty($langContent))
							file_put_contents($this->_generic->getPathConfig("actionLangs").$controllerBo."/__".$controllerBo.".".$lang.".lang.php",$langContent);
					}
					
					// MySQL multilanguage tables
					$handle = opendir($this->_generic->getPathConfig("models"));
					
					// Disable explain
					SLS_Sql::getInstance()->_explain = false;
					
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
								if ($object->isMultilanguage())
								{
									$results = $object->searchModels($object->getTable(),array(),array(0=>array("column"=>"pk_lang","value"=>$defaultLang,"mode"=>"equal")));
									
									for($i=0 ; $i<$count=count($results) ; $i++)
									{
										$object = new $className();
										$object->setModelLanguage($lang);
										foreach($results[$i] as $column => $col_value)
										{
											if ($column != "pk_lang" && $column != $object->getPrimaryKey())
											{													
												$object->__set($column,$col_value);
											}
										}
										try {
											$object->create($results[$i]->{$object->getPrimaryKey()});
										}
										catch (Exception $e){}
									}									
								}								
							}
						}
					}
					// /MySQL multilanguage tables
					
					// Redirect
					$controllers = $this->_generic->getTranslatedController("SLS_Bo","Langs");
					$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"].".sls");
				}
				// Else, form errors
				else
				{
					$xml->startTag("errors");
					foreach($errors as $error)
						$xml->addFullTag("error",$error,true);
					$xml->endTag("errors");
				}				
			}
			
			$xml->startTag("generic_langs");
			foreach($generics as $key => $value)
			{
				$xml->startTag("generic_lang");	
				$xml->addFullTag("key",$key,true);
				$xml->addFullTag("value",$value,true);
				$xml->endTag("generic_lang");
			}
			$xml->endTag("generic_langs");
						
			$xml->startTag("controllers");
			for($i=0 ; $i<$count=count($controllers) ; $i++)
			{
				$xml->startTag("controller");
				$xml->addFullTag("id",$controllers[$i]["id"],true);
				$xml->addFullTag("key",$controllers[$i]["key"],true);
				$xml->startTag("values");
				foreach($controllers[$i]["values"] as $key2 => $value2)
				{
					$xml->startTag("value");
					$xml->addFullTag("key",$key2,true);
					$xml->addFullTag("value",$value2,true);
					$xml->endTag("value");
				}
				$xml->endTag("values");
				$xml->startTag("actions");
				for($j=0 ; $j<$count2=count($controllers[$i]["actions"]) ; $j++)
				{
					$action = $controllers[$i]["actions"];
					$xml->startTag("action");
					$xml->addFullTag("id",$action[$j]["id"],true);
					$xml->addFullTag("key",$action[$j]["key"],true);
					$xml->startTag("values");
					foreach($action[$j]["values"] as $key3 => $value3)
					{
						$xml->startTag("value");
						$xml->addFullTag("key",$key3,true);
						$xml->addFullTag("value",$value3,true);
						$xml->endTag("value");
					}
					$xml->endTag("values");
					$xml->startTag("metas");
					foreach($action[$j]["metas"] as $key4 => $value4)
					{
						$xml->startTag("meta");
						$xml->addFullTag("key",$key4,true);
						$xml->startTag("values");
						foreach($value4 as $key5 => $value5)
						{
							$xml->startTag("value");
							$xml->addFullTag("key",$key5,true);
							$xml->addFullTag("value",$value5,true);
							$xml->endTag("value");
						}
						$xml->endTag("values");
						$xml->endTag("meta");
					}
					$xml->endTag("metas");
					$xml->endTag("action");
				}
				$xml->endTag("actions");
				$xml->endTag("controller");
			}
			$xml->endTag("controllers");
		}
		
		$xpathLangs = $handle->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[@iso != '']");
		foreach ($xpathLangs as $lang)
			if (!in_array(trim($lang), $langs))
				array_push($langs, trim($lang));
		array_multisort($langs, SORT_STRING, SORT_ASC);
		$xml->startTag("langs");
		foreach ($langs as $lang)
			$xml->addFullTag("lang", $lang, true);
		$xml->endTag("langs");
		
		$this->saveXML($xml);
	}	
}
?>