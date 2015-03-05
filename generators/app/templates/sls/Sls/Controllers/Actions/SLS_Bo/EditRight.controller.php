<?php
class SLS_BoEditRight extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		// Objects
		$xml = $this->getXML();
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		$autoActions = array("BoDashBoard", "BoDeleteFile", "BoExport", "BoFkAc", "BoIsLogged", "BoLike", "BoSetting", "BoUnique", "BoUpload", "BoUploadProgress");
			
		$name = SLS_String::trimSlashesFromString($this->_http->getParam("name"));
		$xmlColors = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo_colors.xml"));
		$xmlRights = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml"));
		$result = $xmlRights->getTags("//sls_configs/entry[@login='".($name)."']");
		$db = SLS_Sql::getInstance();
		
		if (empty($result))
		{
			$xml->startTag("errors");
			$xml->addFullTag("error","This account doesn't exists",true);
			$xml->endTag("errors");
		}
		else
		{
			$login 	= $name;
			$pwd 	= "";
			$actions = array("read"   => array(),
							 "add" 	  => array(),
							 "edit"   => array(),
							 "delete" => array(),
							 "clone"  => array(),
							 "email" => array(),
							 "custom" => array());
			$_publicActions = array("BoLogin",
									"BoLogout",
									"BoForgottenPwd",
									"BoMenu", 
									"BoRenewPwd",
									"BoSwitchLang");
			$_forbiddenActions = array("BoPopulate");
		
			if ($db->tableExists("sls_graph"))
			{
				$actions["dashboard"] = array();
				$dashboardId = $this->_generic->getControllersXML()->getTag("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='BoDashBoard']/@id");
				$this->_generic->useModel("Sls_graph",$this->defaultDb,"sls");
				$className = ucfirst($this->defaultDb)."_Sls_graph";
				$slsGraph = new $className;
				$slsGraphs = $slsGraph->searchModels("sls_graph",array(),array(),array(),array("sls_graph_title" => "asc"));
				for($i=0 ; $i<$count=count($slsGraphs) ; $i++)
					$actions["dashboard"][$dashboardId."_sls_graph_".$slsGraphs[$i]->sls_graph_id] = "_".$slsGraphs[$i]->sls_graph_title;
			}
			
			$results = $this->_generic->getControllersXML()->getTagsAttributes("//controllers/controller[@isBo='true']/scontrollers/scontroller",array("name","id"));
			for($i=0 ; $i<$count=count($results) ; $i++)
			{
				$name = $results[$i]["attributes"][0]["value"];
				$aid = $results[$i]["attributes"][1]["value"];
				switch($name)
				{
					case (SLS_String::startsWith($name,"List") && SLS_String::contains($name,"_")):
						$actions["read"][$aid] = $name;		
						break;
					case (SLS_String::startsWith($name,"Add") && SLS_String::contains($name,"_")):
						$actions["add"][$aid] = $name;		
						break;
					case (SLS_String::startsWith($name,"Modify") && SLS_String::contains($name,"_")):
						$actions["edit"][$aid] = $name;		
						break;
					case (SLS_String::startsWith($name,"Delete") && SLS_String::contains($name,"_")):
						$actions["delete"][$aid] = $name;		
						break;
					case (SLS_String::startsWith($name,"Clone") && SLS_String::contains($name,"_")):
						$actions["clone"][$aid] = $name;		
						break;
					case (SLS_String::startsWith($name,"Email") && SLS_String::contains($name,"_")):
						$actions["email"][$aid] = $name;		
						break;
					default:
						if (!in_array($name,$_publicActions) && !in_array($name,$_forbiddenActions) && !in_array($name,$autoActions))
						{
							$id = array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".$name."']/@id"));
							if (!empty($id))
								$actions["custom"][$id] = "_".$name;
						}
						break;
				}
			}
						
			$xml->startTag("bo_groups");
			foreach($actions as $action => $values)
			{
				$xml->startTag("bo_group");
				$xml->addFullTag("name",$action,true);
				foreach($values as $aid => $name)
				{
					$model = "";
					$selected = $xmlRights->getTag("//sls_configs/entry[@login='".($login)."']/action[@id='".$aid."']/@id");
										
					switch ($name)
					{
						case (SLS_String::startsWith($name,"List")): 	$model = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($name,"List"),"_"); 	break;
						case (SLS_String::startsWith($name,"Add")): 	$model = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($name,"Add"),"_"); 	break;
						case (SLS_String::startsWith($name,"Modify")): 	$model = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($name,"Modify"),"_"); break;
						case (SLS_String::startsWith($name,"Delete")): 	$model = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($name,"Delete"),"_"); break;
						case (SLS_String::startsWith($name,"Clone")): 	$model = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($name,"Clone"),"_"); 	break;
						case (SLS_String::startsWith($name,"Email")): 	$model = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($name,"Email"),"_"); 	break;
						default:										$model = "";
					}
					$xml->startTag("action");
						$xml->addFullTag("name",SLS_String::substrAfterFirstDelimiter($name,"_"),true);
						$xml->addFullTag("model",$model,true);
						$xml->addFullTag("id",$aid,true);
						$xml->addFullTag("selected",(!empty($selected)) ? "true" : "false",true);
					$xml->endTag("action");
				}
				$xml->endTag("bo_group");
			}
			$xml->endTag("bo_groups");
			
			$complexity_pwd_lc 				= (SLS_String::contains($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"lc")) ? "true" : "";
			$complexity_pwd_uc 				= (SLS_String::contains($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"uc")) ? "true" : "";
			$complexity_pwd_digit 			= (SLS_String::contains($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"digit")) ? "true" : "";
			$complexity_pwd_special_char 	= (SLS_String::contains($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"special_char")) ? "true" : "";
			$enabled 						= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@enabled') == "true") ? "true": "false";
			$color 							= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/settings/setting[@key="color"]') == null) ? "pink" : $xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/settings/setting[@key="color"]');
			$complexity_pwd_min_char 		= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@min_chars_pwd') == "") ? "" : "true";
			$complexity_pwd_min_char_nb 	= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@min_chars_pwd') == "") ? "8" : $xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@min_chars_pwd');
			$reset_pwd 						= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@reset_pwd') == "") ? "" : "true";
			$renew_pwd 						= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd') == "") ? "" : "true";
			$renew_pwd_nb 					= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd') == "") ? "2" : trim(SLS_String::substrBeforeFirstDelimiter($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd')," "));
			$renew_pwd_unite 				= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd') == "") ? "month" : trim(SLS_String::substrAfterFirstDelimiter($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd')," "));
			$renew_pwd_log 					= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd_nb') == "") ? "" : "true";
			$renew_pwd_log_nb				= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd_nb') == "") ? "3" : $xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd_nb');
			$lastname 						= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@name') == "") ? "" : $xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@name');
			$firstname						= ($xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@firstname') == "") ? "" : $xmlRights->getTag('//sls_configs/entry[@login="'.($login).'"]/@firstname');
			
			if ($this->_http->getParam("reload") == "true")
			{
				$complexity = array();
				$privilegeChoose = false;
				$minChars = "";
				$errors = array();

				$lastname 						= SLS_String::trimSlashesFromString($this->_http->getParam("lastname"));
				$firstname						= SLS_String::trimSlashesFromString($this->_http->getParam("firstname"));
				$enabled						= SLS_String::trimSlashesFromString($this->_http->getParam("enabled"));
				$pwd 							= SLS_String::trimSlashesFromString($this->_http->getParam("password"));
				$complexity_pwd_lc 				= SLS_String::trimSlashesFromString($this->_http->getParam("complexity_pwd_lc"));
				$complexity_pwd_uc 				= SLS_String::trimSlashesFromString($this->_http->getParam("complexity_pwd_uc"));
				$complexity_pwd_digit 			= SLS_String::trimSlashesFromString($this->_http->getParam("complexity_pwd_digit"));
				$complexity_pwd_special_char 	= SLS_String::trimSlashesFromString($this->_http->getParam("complexity_pwd_special_char"));
				$complexity_pwd_min_char 		= SLS_String::trimSlashesFromString($this->_http->getParam("complexity_pwd_min_char"));
				$complexity_pwd_min_char_nb 	= SLS_String::trimSlashesFromString($this->_http->getParam("complexity_pwd_min_char_nb"));
				$reset_pwd 						= SLS_String::trimSlashesFromString($this->_http->getParam("reset_pwd"));
				$renew_pwd 						= SLS_String::trimSlashesFromString($this->_http->getParam("renew_pwd"));
				$renew_pwd_nb 					= SLS_String::trimSlashesFromString($this->_http->getParam("renew_pwd_nb"));
				$renew_pwd_unite 				= SLS_String::trimSlashesFromString($this->_http->getParam("renew_pwd_unite"));
				$renew_pwd_log 					= SLS_String::trimSlashesFromString($this->_http->getParam("renew_pwd_log"));
				$renew_pwd_log_nb 				= SLS_String::trimSlashesFromString($this->_http->getParam("renew_pwd_log_nb"));
				$color 							= SLS_String::trimSlashesFromString($this->_http->getParam("color"));
				$params 						= $this->_http->getParams();				
				
				foreach($params as $key => $value)
				{				
					if (SLS_String::startsWith($key,"bo_action"))
					{
						$privilegeChoose = true;
						break;
					}
				}
				if (!$privilegeChoose)			
					array_push($errors,"You must choose at least 1 privilege.");
				
				if (empty($lastname) || empty($firstname))
					array_push($errors,"You must fill name and firstname.");
					
				if ($complexity_pwd_lc == "true")
					array_push($complexity,"lc");
				if ($complexity_pwd_uc == "true")
					array_push($complexity,"uc");
				if ($complexity_pwd_digit == "true")
					array_push($complexity,"digit");
				if ($complexity_pwd_special_char == "true")
					array_push($complexity,"special_char");
					
				if ($complexity_pwd_min_char == "true")
					$minChars = $complexity_pwd_min_char_nb;
				
				if (empty($errors))
				{
					if (!empty($pwd))
						$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("password" => sha1($pwd)));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("name" => SLS_String::stringToUrl($lastname," ")));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("firstname" => SLS_String::stringToUrl($firstname," ")));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("enabled" => $enabled));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("last_renew_pwd" => date("Y-m-d")));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("complexity_pwd" => implode("|",$complexity)));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("min_chars_pwd" => $minChars));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("reset_pwd" => (($reset_pwd=="true") ? "true" : "")));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("renew_pwd" => (($renew_pwd=="true") ? $renew_pwd_nb." ".$renew_pwd_unite : "")));
					$xmlRights->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("renew_pwd_nb" => (($renew_pwd_log=="true") ? $renew_pwd_log_nb : "")));
					
					# Delete old privileges
					$xmlRights->deleteTags('//sls_configs/entry[@login="'.($login).'"]/action', count($xmlRights->getTags('//sls_configs/entry[@login="'.($login).'"]/action')));
					$xmlRights->setTag('//sls_configs/entry[@login="'.($login).'"]/settings/setting[@key="color"]',(empty($color)) ? "pink" : $color);
					$xmlRights->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml",$xmlRights->getXML());			
					$xmlRights = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml"));
					$xmlNew = "\n";
					foreach($autoActions as $actionName)
					{
						$aid = $this->_generic->getControllersXML()->getTag("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".$actionName."']/@id");
						$xmlNew .= '    <action id="'.$aid.'" role="custom" entity="" />'."\n";
					}
					foreach($params as $key => $value)
					{
						if (SLS_String::startsWith($key,"bo_action"))
						{
							$xmlNew .= '    <action id="'.$value.'" role="'.SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($key,"bo_action_"),"_").'" entity="'.((SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($key,"bo_action_"),"_") == "custom") ? "" : SLS_String::substrAfterFirstDelimiter(SLS_String::substrAfterFirstDelimiter($key,"bo_action_"),"_")).'" />'."\n";
						}
					}					
					$xmlRights->appendXMLNode('//sls_configs/entry[@login="'.($login).'"]',$xmlNew);
					$xmlRights->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml",$xmlRights->getXML());
					$this->_generic->redirect("Manage/Rights");
				}
				else
				{
					$xml->startTag("errors");
					foreach($errors as $error)
						$xml->addFullTag("error",$error,true);
					$xml->endTag("errors");
				}
			}
			
			$xml->addFullTag("complexity_pwd_lc",$complexity_pwd_lc,true);
			$xml->addFullTag("complexity_pwd_uc",$complexity_pwd_uc,true);
			$xml->addFullTag("complexity_pwd_digit",$complexity_pwd_digit,true);
			$xml->addFullTag("complexity_pwd_special_char",$complexity_pwd_special_char,true);
			$xml->addFullTag("complexity_pwd_min_char",$complexity_pwd_min_char,true);
			$xml->addFullTag("complexity_pwd_min_char_nb",$complexity_pwd_min_char_nb,true);
			$xml->addFullTag("reset_pwd",$reset_pwd,true);
			$xml->addFullTag("renew_pwd",$renew_pwd,true);
			$xml->addFullTag("renew_pwd_nb",$renew_pwd_nb,true);
			$xml->addFullTag("renew_pwd_unite",$renew_pwd_unite,true);
			$xml->addFullTag("renew_pwd_log",$renew_pwd_log,true);
			$xml->addFullTag("renew_pwd_log_nb",$renew_pwd_log_nb,true);
			$xml->addFullTag("login",$login,true);
			$xml->addFullTag("enabled",$enabled,true);
			$xml->addFullTag("color",$color,true);
			$xml->addFullTag("name",ucwords(strtolower($lastname)),true);
			$xml->addFullTag("firstname",ucwords(strtolower($firstname)),true);
			$colors = $xmlColors->getTagsAttributes("//sls_configs/template",array("name","hexa"));
			$xml->startTag("colors");
			for($i=0 ; $i<$count=count($colors) ; $i++)
				$xml->addFullTag("color",$colors[$i]["attributes"][0]["value"],true,array("hexa" => $colors[$i]["attributes"][1]["value"]));
			$xml->endTag("colors");
		}
		$this->saveXML($xml);
	}
	
}
?>