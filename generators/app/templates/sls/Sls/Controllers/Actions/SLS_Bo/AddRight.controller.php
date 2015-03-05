<?php
class SLS_BoAddRight extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		// Objects
		$xml = $this->getXML();
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		$actions = array("read"   => array(),
						 "add" 	  => array(),
						 "edit"   => array(),
						 "delete" => array(),
						 "clone"  => array(),
						 "email"  => array(),
						 "custom" => array());
		$autoActions = array("BoDashBoard", "BoDeleteFile", "BoExport", "BoFkAc", "BoIsLogged", "BoLike", "BoSetting", "BoUnique", "BoUpload", "BoUploadProgress");
		$_publicActions = array("BoLogin",
								"BoLogout",
								"BoForgottenPwd",
								"BoMenu", 
								"BoRenewPwd",
								"BoSwitchLang");
		$_forbiddenActions = array("BoPopulate");
		$db = SLS_Sql::getInstance();
		$color = "pink";
		$xmlColors = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo_colors.xml"));
		$errors = array();
		
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
				$xml->endTag("action");
			}
			$xml->endTag("bo_group");
		}
		$xml->endTag("bo_groups");
		
		$complexity_pwd_min_char_nb = 8;
		$renew_pwd_nb = 2;
		$renew_pwd_unite = "month";
		$renew_pwd_log_nb = 3;
		$complexity = array();
		$privilegeChoose = false;
		$minChars = "";
		$enabled = "true";
		
		if ($this->_http->getParam("reload") == "true")
		{
			$login 							= SLS_String::trimSlashesFromString($this->_http->getParam("login"));
			$name 							= SLS_String::trimSlashesFromString($this->_http->getParam("name"));
			$firstname						= SLS_String::trimSlashesFromString($this->_http->getParam("firstname"));
			$pwd 							= SLS_String::trimSlashesFromString($this->_http->getParam("password"));
			$enabled						= SLS_String::trimSlashesFromString($this->_http->getParam("enabled"));
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
			
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml");
			$xmlRights = new SLS_XMLToolbox($pathsHandle);
			$result = $xmlRights->getTags("//sls_configs/entry[@login='".($login)."']");
			
			if (!empty($result))			
				array_push($errors,"This account already exists, please choose another login.");
			if (empty($name) || empty($firstname))
				array_push($errors,"You must fill name and firstname.");
			if (empty($login) || empty($pwd))
				array_push($errors,"You must choose username and password.");
			
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
			
			if (empty($errors))
			{
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
								
				$xmlNew = '<entry login="'.(strtolower($login)).'" name="'.SLS_String::stringToUrl($name," ").'" firstname="'.SLS_String::stringToUrl($firstname," ").'" enabled="'.$enabled.'" password="'.sha1($pwd).'" password_old="" last_connection="" last_renew_pwd="'.date("Y-m-d").'" complexity_pwd="'.implode("|",$complexity).'" min_chars_pwd="'.$minChars.'" reset_pwd="'.(($reset_pwd=="true") ? "true" : "").'" renew_pwd="'.(($renew_pwd=="true") ? $renew_pwd_nb." ".$renew_pwd_unite : "").'" renew_pwd_nb="'.(($renew_pwd_log=="true") ? $renew_pwd_log_nb : "").'">'."\n";
				
				// Default settings
				$xmlNew .= '    <settings>'."\n".
							'      <setting key="nav_filter"><![CDATA[default]]></setting>'."\n".
							'      <setting key="list_view"><![CDATA[collapse]]></setting>'."\n".
							'      <setting key="list_nb_by_page"><![CDATA[20]]></setting>'."\n".
							'      <setting key="add_callback"><![CDATA[list]]></setting>'."\n".
							'      <setting key="edit_callback"><![CDATA[list]]></setting>'."\n".
							'      <setting key="export_format"><![CDATA[excel]]></setting>'."\n".
							'      <setting key="export_all_column"><![CDATA[true]]></setting>'."\n".
							'      <setting key="export_all_table"><![CDATA[true]]></setting>'."\n".
							'      <setting key="export_display_legend"><![CDATA[true]]></setting>'."\n".
							'      <setting key="quick_edit"><![CDATA[disabled]]></setting>'."\n".
							'      <setting key="dashboard_ga"><![CDATA[visible]]></setting>'."\n".
							'      <setting key="dashboard_metric"><![CDATA[visible]]></setting>'."\n".
							'      <setting key="dashboard_monitoring"><![CDATA[visible]]></setting>'."\n".
							'      <setting key="dashboard_graph"><![CDATA[visible]]></setting>'."\n".
							'      <setting key="dashboard_email"><![CDATA[visible]]></setting>'."\n".
							'      <setting key="color"><![CDATA['.((empty($color)) ? "pink" : $color).']]></setting>'."\n".
							'    </settings>'."\n";
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
				
				$xmlNew .= '  </entry>';				
				$xmlRights->appendXMLNode("//sls_configs",$xmlNew);
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
		$xml->addFullTag("enabled",$enabled,true);
		$xml->addFullTag("color",(empty($color)) ? "pink" : $color,true);
		$colors = $xmlColors->getTagsAttributes("//sls_configs/template",array("name","hexa"));
		$xml->startTag("colors");
		for($i=0 ; $i<$count=count($colors) ; $i++)
			$xml->addFullTag("color",$colors[$i]["attributes"][0]["value"],true,array("hexa" => $colors[$i]["attributes"][1]["value"]));
		$xml->endTag("colors");
		
		$xml->addFullTag("url_generate_bo",$this->_generic->getFullPath("SLS_Bo","GenerateBo"),true);
		
		$this->saveXML($xml);
	}
	
}
?>